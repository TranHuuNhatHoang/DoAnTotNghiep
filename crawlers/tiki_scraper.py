import re
import sys
import time
from datetime import datetime, timedelta
from html import unescape

import mysql.connector
import requests

from app_config import get_db_config, load_env
from bot_lock import FileLock
from crawler_report import CrawlerRunReport


try:
    sys.stdout.reconfigure(encoding="utf-8")
except AttributeError:
    pass


load_env()

STATUS_SUCCESS = 1
STATUS_NO_PRICE = 2
STATUS_ERROR = 3
EXIT_OK = 0
EXIT_FATAL = 1

AVAILABILITY_UNKNOWN = "unknown"
AVAILABILITY_ACTIVE = "active"
AVAILABILITY_TEMPORARILY_UNAVAILABLE = "temporarily_unavailable"
AVAILABILITY_DISCONTINUED = "discontinued"
AVAILABILITY_INVALID_URL = "invalid_url"
AVAILABILITY_FETCH_ERROR = "fetch_error"


def env_int(name, default, minimum=None, maximum=None):
    import os

    try:
        value = int(os.getenv(name, str(default)))
    except (TypeError, ValueError):
        value = default
    if minimum is not None:
        value = max(minimum, value)
    if maximum is not None:
        value = min(maximum, value)
    return value


TIKI_BATCH_LIMIT = env_int("TIKI_BATCH_LIMIT", 0, minimum=0)
TIKI_REQUEST_TIMEOUT = env_int("TIKI_REQUEST_TIMEOUT", 12, minimum=3, maximum=60)
TIKI_RETRY_COUNT = env_int("TIKI_RETRY_COUNT", 2, minimum=0, maximum=5)
TIKI_LOCK_STALE_MINUTES = env_int("TIKI_LOCK_STALE_MINUTES", 60, minimum=10)
TIKI_SUCCESS_INTERVAL_MINUTES = env_int("TIKI_SUCCESS_INTERVAL_MINUTES", 240, minimum=30)
TIKI_ERROR_RETRY_MINUTES = env_int("TIKI_ERROR_RETRY_MINUTES", 60, minimum=15)
TIKI_NO_PRICE_RETRY_MINUTES = env_int("TIKI_NO_PRICE_RETRY_MINUTES", 720, minimum=30)
TIKI_FINAL_STATUS_FAILURES = env_int("TIKI_FINAL_STATUS_FAILURES", 3, minimum=2, maximum=10)
TIKI_MAX_RETRY_DELAY_MINUTES = env_int("TIKI_MAX_RETRY_DELAY_MINUTES", 1440, minimum=60)


def log(message):
    print(message, flush=True)


def format_vn_number(value):
    try:
        return f"{int(value):,}".replace(",", ".")
    except (TypeError, ValueError):
        return str(value)


def minutes_from_now(minutes):
    return (datetime.now() + timedelta(minutes=int(minutes))).strftime("%Y-%m-%d %H:%M:%S")


def sanitize_error_message(message):
    text = re.sub(r"\s+", " ", str(message or "")).strip()
    if not text:
        return None
    return text[:500]


def retry_delay_minutes(base_minutes, failure_count):
    multiplier = max(1, int(failure_count or 0))
    return min(int(base_minutes) * multiplier, TIKI_MAX_RETRY_DELAY_MINUTES)


def extract_tiki_ids(url):
    product_id_match = re.search(r"-p(\d+)\.html", url)
    spid_match = re.search(r"spid=(\d+)", url)
    product_id = product_id_match.group(1) if product_id_match else None
    spid = spid_match.group(1) if spid_match else None
    return product_id, spid


def normalize_image_url(url):
    if not url:
        return None

    url = str(url).strip()
    if not url:
        return None
    if url.startswith("//"):
        return "https:" + url
    if url.startswith("/"):
        return "https://salt.tikicdn.com" + url
    return url


def extract_thumbnail_url(product_data):
    direct_url = normalize_image_url(product_data.get("thumbnail_url"))
    if direct_url:
        return direct_url

    for image in product_data.get("images", []) or []:
        for key in ("base_url", "large_url", "medium_url", "small_url", "thumbnail_url"):
            image_url = normalize_image_url(image.get(key))
            if image_url:
                return image_url

    return None


def clean_text(value):
    if isinstance(value, (list, tuple)):
        parts = [clean_text(item) for item in value]
        return ", ".join(part for part in parts if part)
    if isinstance(value, dict):
        value = value.get("value") or value.get("name") or value.get("text") or ""

    text = unescape(str(value or ""))
    text = re.sub(r"<[^>]+>", " ", text)
    return re.sub(r"\s+", " ", text).strip()


def extract_specifications(product_data):
    specifications = []
    display_order = 0

    for group in product_data.get("specifications", []) or []:
        group_name = clean_text(group.get("name")) or "Thông tin sản phẩm"
        for attribute in group.get("attributes", []) or []:
            spec_name = clean_text(attribute.get("name") or attribute.get("label"))
            spec_value = clean_text(attribute.get("value"))

            if not spec_name or not spec_value:
                continue

            display_order += 1
            specifications.append(
                {
                    "group_name": group_name,
                    "spec_name": spec_name,
                    "spec_value": spec_value,
                    "display_order": display_order,
                }
            )

    return specifications


def build_tiki_api_url(product_url):
    product_id, spid = extract_tiki_ids(product_url)
    if not product_id:
        return None

    api_url = f"https://tiki.vn/api/v2/products/{product_id}?platform=web&version=3"
    if spid:
        api_url += f"&spid={spid}"
    return api_url


def request_tiki_api(api_url):
    headers = {
        "User-Agent": (
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
            "AppleWebKit/537.36 (KHTML, like Gecko) "
            "Chrome/120.0.0.0 Safari/537.36"
        ),
        "Accept": "application/json, text/plain, */*",
        "Referer": "https://tiki.vn/",
    }

    last_error = None
    for attempt in range(1, TIKI_RETRY_COUNT + 2):
        try:
            response = requests.get(api_url, headers=headers, timeout=TIKI_REQUEST_TIMEOUT)
            if response.status_code == 200:
                return response.json(), None
            if response.status_code in {404, 410}:
                return None, {
                    "status": STATUS_NO_PRICE,
                    "availability_status": AVAILABILITY_DISCONTINUED,
                    "message": f"HTTP {response.status_code}",
                }
            last_error = f"HTTP {response.status_code}"
        except requests.exceptions.RequestException as exc:
            last_error = str(exc)

        if attempt <= TIKI_RETRY_COUNT:
            log(f"  [thử lại] Tiki chưa phản hồi ổn định ({last_error}). Thử lại lần {attempt}/{TIKI_RETRY_COUNT}.")
            time.sleep(1.2 * attempt)

    return None, {"status": STATUS_ERROR, "message": last_error or "Không rõ lỗi"}


def scrape_tiki_data(product_url):
    log(f"[Tiki] Đang xử lý link: {product_url}")
    api_url = build_tiki_api_url(product_url)
    if not api_url:
        log("  [lỗi] Không trích xuất được Product ID từ link Tiki.")
        return {
            "status": STATUS_ERROR,
            "availability_status": AVAILABILITY_INVALID_URL,
            "error_message": "URL Tiki không hợp lệ hoặc thiếu product id",
        }

    data, error = request_tiki_api(api_url)
    if error:
        log(f"  [lỗi] Không lấy được dữ liệu Tiki: {error['message']}")
        return {
            "status": error["status"],
            "availability_status": error.get("availability_status", AVAILABILITY_FETCH_ERROR),
            "error_message": error["message"],
        }

    current_price = data.get("price") or 0
    if current_price <= 0:
        log("  [bỏ qua] API Tiki không trả về giá hợp lệ.")
        return {
            "status": STATUS_NO_PRICE,
            "availability_status": AVAILABILITY_TEMPORARILY_UNAVAILABLE,
            "error_message": "API Tiki không trả về giá hợp lệ",
        }

    result = {
        "name": data.get("name"),
        "thumbnail_url": extract_thumbnail_url(data),
        "current_price": current_price,
        "original_price": data.get("original_price"),
        "historical_sold": data.get("quantity_sold", {}).get("value", 0),
        "rating_average": data.get("rating_average", 0),
        "review_count": data.get("review_count", 0),
        "specifications": extract_specifications(data),
        "status": STATUS_SUCCESS,
        "availability_status": AVAILABILITY_ACTIVE,
    }

    log(f"  [thành công] Giá hiện tại: {format_vn_number(current_price)} đ")
    if result["thumbnail_url"]:
        log("  [thành công] Đã lấy được link ảnh sản phẩm.")
    if result["specifications"]:
        log(f"  [thành công] Đã lấy được {len(result['specifications'])} dòng thông số sản phẩm.")
    return result


def fetch_tiki_links(cursor):
    sql = (
        "SELECT id, product_id, product_url FROM platform_links "
        "WHERE platform_name = 'Tiki' AND is_active = 1 "
        "AND (next_check_at IS NULL OR next_check_at <= NOW()) "
        "AND (next_scrape_at IS NULL OR next_scrape_at <= NOW()) "
        "ORDER BY last_scraped_at ASC"
    )
    if TIKI_BATCH_LIMIT > 0:
        sql += f" LIMIT {TIKI_BATCH_LIMIT}"
    cursor.execute(sql)
    return cursor.fetchall()


def ensure_product_specifications_table(cursor):
    cursor.execute(
        """
        CREATE TABLE IF NOT EXISTS product_specifications (
          id INT AUTO_INCREMENT PRIMARY KEY,
          product_id INT NOT NULL,
          group_name VARCHAR(255) NOT NULL DEFAULT 'Thông tin sản phẩm',
          spec_name VARCHAR(255) NOT NULL,
          spec_value TEXT NOT NULL,
          display_order INT NOT NULL DEFAULT 0,
          source_platform ENUM('Tiki', 'Shopee', 'Lazada', 'Manual') NOT NULL DEFAULT 'Manual',
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          KEY idx_product_specs_product_order (product_id, display_order, id),
          KEY idx_product_specs_name (spec_name),
          CONSTRAINT fk_product_specs_product
            FOREIGN KEY (product_id) REFERENCES products(id)
            ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        """
    )


def save_product_specifications(cursor, product_id, specifications):
    if not specifications:
        return 0

    cursor.execute(
        "DELETE FROM product_specifications WHERE product_id = %s AND source_platform = %s",
        (product_id, "Tiki"),
    )
    cursor.executemany(
        """
        INSERT INTO product_specifications
            (product_id, group_name, spec_name, spec_value, display_order, source_platform)
        VALUES (%s, %s, %s, %s, %s, %s)
        """,
        [
            (
                product_id,
                item["group_name"],
                item["spec_name"],
                item["spec_value"],
                item["display_order"],
                "Tiki",
            )
            for item in specifications
        ],
    )
    return len(specifications)


def save_tiki_data(cursor, conn, link, data):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    next_time = minutes_from_now(TIKI_SUCCESS_INTERVAL_MINUTES)
    link_id = link["id"]
    product_id = link["product_id"]

    cursor.execute(
        """
        UPDATE platform_links
        SET current_price = %s,
            original_price = %s,
            historical_sold = %s,
            rating_average = %s,
            review_count = %s,
            status = %s,
            availability_status = %s,
            error_message = NULL,
            last_scraped_at = %s,
            last_checked_at = %s,
            next_scrape_at = %s,
            next_check_at = %s,
            blocked_until = NULL,
            retry_count = 0,
            consecutive_failures = 0
        WHERE id = %s
        """,
        (
            data["current_price"],
            data["original_price"],
            data["historical_sold"],
            data["rating_average"],
            data["review_count"],
            data["status"],
            data.get("availability_status", AVAILABILITY_ACTIVE),
            now,
            now,
            next_time,
            next_time,
            link_id,
        ),
    )
    cursor.execute(
        "INSERT INTO price_history (link_id, price, scraped_at) VALUES (%s, %s, %s)",
        (link_id, data["current_price"], now),
    )

    if data.get("thumbnail_url"):
        cursor.execute(
            "UPDATE products SET thumbnail_url = %s WHERE id = %s",
            (data["thumbnail_url"], product_id),
        )
        log(f"  [thành công] Đã cập nhật thumbnail_url cho product ID={product_id}.")

    saved_specs = save_product_specifications(cursor, product_id, data.get("specifications") or [])
    if saved_specs:
        log(f"  [thành công] Đã lưu {saved_specs} dòng thông số sản phẩm.")

    conn.commit()


def get_consecutive_failures(cursor, link_id):
    cursor.execute("SELECT consecutive_failures FROM platform_links WHERE id = %s", (link_id,))
    row = cursor.fetchone()
    if not row:
        return 0
    return int(row.get("consecutive_failures") or 0)


def finalize_availability_status(cursor, link_id, requested_status):
    if requested_status not in {AVAILABILITY_DISCONTINUED, AVAILABILITY_TEMPORARILY_UNAVAILABLE}:
        return requested_status

    next_failure_count = get_consecutive_failures(cursor, link_id) + 1
    if next_failure_count >= TIKI_FINAL_STATUS_FAILURES:
        return requested_status
    return AVAILABILITY_FETCH_ERROR


def update_status(cursor, conn, link_id, status, availability_status=AVAILABILITY_FETCH_ERROR, error_message=None):
    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    final_availability_status = finalize_availability_status(cursor, link_id, availability_status)
    next_failure_count = get_consecutive_failures(cursor, link_id) + 1

    if final_availability_status == AVAILABILITY_INVALID_URL:
        delay_minutes = TIKI_MAX_RETRY_DELAY_MINUTES
    elif final_availability_status in {AVAILABILITY_DISCONTINUED, AVAILABILITY_TEMPORARILY_UNAVAILABLE}:
        delay_minutes = TIKI_NO_PRICE_RETRY_MINUTES
    else:
        delay_minutes = retry_delay_minutes(TIKI_ERROR_RETRY_MINUTES, next_failure_count)

    next_time = minutes_from_now(delay_minutes)
    cursor.execute(
        """
        UPDATE platform_links
        SET status = %s,
            availability_status = %s,
            error_message = %s,
            last_scraped_at = %s,
            last_checked_at = %s,
            next_scrape_at = %s,
            next_check_at = %s,
            blocked_until = NULL,
            retry_count = retry_count + 1,
            consecutive_failures = consecutive_failures + 1
        WHERE id = %s
        """,
        (
            status,
            final_availability_status,
            sanitize_error_message(error_message),
            now,
            now,
            next_time,
            next_time,
            link_id,
        ),
    )
    conn.commit()


def update_tiki_prices_to_db():
    conn = None
    cursor = None
    exit_code = EXIT_OK
    report = CrawlerRunReport(
        "Tiki",
        config={
            "batch_limit": TIKI_BATCH_LIMIT,
            "request_timeout": TIKI_REQUEST_TIMEOUT,
            "retry_count": TIKI_RETRY_COUNT,
        },
    )

    try:
        log("Bắt đầu chạy bot Tiki")
        log(
            "Cấu hình: "
            f"số link mỗi lượt={'tất cả' if TIKI_BATCH_LIMIT == 0 else TIKI_BATCH_LIMIT}, "
            f"timeout={TIKI_REQUEST_TIMEOUT} giây, số lần thử lại={TIKI_RETRY_COUNT}"
        )

        conn = mysql.connector.connect(**get_db_config())
        cursor = conn.cursor(dictionary=True)
        ensure_product_specifications_table(cursor)
        conn.commit()
        tiki_links = fetch_tiki_links(cursor)
        report.set_total_candidates(len(tiki_links))

        log(f"Tìm thấy {len(tiki_links)} link Tiki cần cập nhật.")
        if not tiki_links:
            return exit_code

        for index, link in enumerate(tiki_links, start=1):
            link_started = time.perf_counter()
            log("-" * 60)
            log(f"[{index}/{len(tiki_links)}] Đang quét link Tiki ID={link['id']}")
            try:
                data = scrape_tiki_data(link["product_url"])
                if data and data.get("status") == STATUS_SUCCESS:
                    save_tiki_data(cursor, conn, link, data)
                    report.record_link(
                        link["id"],
                        product_id=link.get("product_id"),
                        status_code=STATUS_SUCCESS,
                        availability_status=AVAILABILITY_ACTIVE,
                        price=data.get("current_price"),
                        elapsed_seconds=time.perf_counter() - link_started,
                    )
                    log("  [thành công] Đã lưu dữ liệu Tiki vào database.")
                else:
                    status = data.get("status", STATUS_ERROR) if data else STATUS_ERROR
                    availability_status = data.get("availability_status", AVAILABILITY_FETCH_ERROR) if data else AVAILABILITY_FETCH_ERROR
                    error_message = data.get("error_message", "Không lấy được dữ liệu Tiki") if data else "Không lấy được dữ liệu Tiki"
                    update_status(cursor, conn, link["id"], status, availability_status, error_message)
                    report.record_link(
                        link["id"],
                        product_id=link.get("product_id"),
                        status_code=status,
                        availability_status=availability_status,
                        elapsed_seconds=time.perf_counter() - link_started,
                        error_message=error_message,
                    )
                    log(f"  [bỏ qua] Không cập nhật được link ID={link['id']}. Trạng thái={status}.")
            except Exception as exc:
                update_status(cursor, conn, link["id"], STATUS_ERROR, AVAILABILITY_FETCH_ERROR, str(exc))
                report.record_link(
                    link["id"],
                    product_id=link.get("product_id"),
                    status_code=STATUS_ERROR,
                    availability_status=AVAILABILITY_FETCH_ERROR,
                    elapsed_seconds=time.perf_counter() - link_started,
                    error_message=str(exc),
                )
                log(f"  [lỗi] Lỗi khi xử lý link ID={link['id']}: {exc}")

        log("Bot Tiki đã hoàn tất lượt cập nhật.")
        return exit_code
    except mysql.connector.Error as err:
        exit_code = EXIT_FATAL
        log(f"[lỗi nghiêm trọng] Lỗi MySQL: {err}")
        return exit_code
    except Exception as exc:
        exit_code = EXIT_FATAL
        log(f"[lỗi nghiêm trọng] Bot Tiki bị lỗi: {exc}")
        return exit_code
    finally:
        if cursor:
            cursor.close()
        if conn and conn.is_connected():
            conn.close()
            log("Đã đóng kết nối database.")
        report_path = report.write(exit_code=exit_code)
        if report_path:
            log(f"[report] Da luu bao cao crawler: {report_path}")


def run_with_lock():
    with FileLock("tiki_crawler", stale_after_minutes=TIKI_LOCK_STALE_MINUTES) as acquired:
        if not acquired:
            log("[bỏ qua] Bot Tiki đang chạy ở tiến trình khác, bỏ qua lượt này.")
            return EXIT_OK
        return update_tiki_prices_to_db()


if __name__ == "__main__":
    sys.exit(run_with_lock())
