import os
import random
import re
import sys
import time
from datetime import datetime, timedelta
from pathlib import Path

import mysql.connector
import undetected_chromedriver as uc
from selenium.common.exceptions import TimeoutException, WebDriverException
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

from app_config import get_chrome_version_main, get_db_config, get_profile_path, load_env
from bot_lock import FileLock


try:
    sys.stdout.reconfigure(encoding="utf-8")
except AttributeError:
    pass


load_env()

STATUS_SUCCESS = 1
STATUS_NO_PRICE = 2
STATUS_ERROR = 3
STATUS_CAPTCHA = 4

EXIT_OK = 0
EXIT_FATAL = 1
EXIT_CAPTCHA = 2

AVAILABILITY_ACTIVE = "active"
AVAILABILITY_OUT_OF_STOCK = "out_of_stock"
AVAILABILITY_TEMPORARILY_UNAVAILABLE = "temporarily_unavailable"
AVAILABILITY_DISCONTINUED = "discontinued"
AVAILABILITY_FETCH_ERROR = "fetch_error"
AVAILABILITY_BLOCKED = "blocked_or_captcha"

BASE_DIR = Path(__file__).resolve().parent
DEBUG_DIR = BASE_DIR / "storage" / "bot_debug"


def env_bool(name, default=False):
    value = os.getenv(name)
    if value is None or value == "":
        return default
    return value.strip().lower() in {"1", "true", "yes", "on"}


def env_int(name, default, minimum=None, maximum=None):
    try:
        value = int(os.getenv(name, str(default)))
    except (TypeError, ValueError):
        value = default
    if minimum is not None:
        value = max(minimum, value)
    if maximum is not None:
        value = min(maximum, value)
    return value


def env_float(name, default, minimum=None, maximum=None):
    try:
        value = float(os.getenv(name, str(default)))
    except (TypeError, ValueError):
        value = default
    if minimum is not None:
        value = max(minimum, value)
    if maximum is not None:
        value = min(maximum, value)
    return value


HEADLESS_MODE = env_bool("LAZADA_HEADLESS", False)
BATCH_LIMIT = env_int("LAZADA_BATCH_LIMIT", 100, minimum=1, maximum=500)
CHUNK_SIZE = env_int("LAZADA_CHUNK_SIZE", 25, minimum=1, maximum=100)
CAPTCHA_COOLDOWN_MINUTES = env_int("LAZADA_CAPTCHA_COOLDOWN_MINUTES", 180, minimum=15)
STOP_ON_CAPTCHA = env_bool("LAZADA_STOP_ON_CAPTCHA", True)
PAGE_LOAD_TIMEOUT = env_int("LAZADA_PAGE_LOAD_TIMEOUT", 45, minimum=10, maximum=180)
MIN_DELAY_SECONDS = env_float("LAZADA_MIN_DELAY_SECONDS", 3.0, minimum=1.0)
MAX_DELAY_SECONDS = env_float("LAZADA_MAX_DELAY_SECONDS", 5.0, minimum=MIN_DELAY_SECONDS)
CHUNK_DELAY_SECONDS = env_int("LAZADA_CHUNK_DELAY_SECONDS", 15, minimum=0)
LOAD_IMAGES = env_bool("LAZADA_LOAD_IMAGES", False)
PROFILE_NAME = os.getenv("LAZADA_PROFILE_NAME", "master_profile")
LOCK_STALE_MINUTES = env_int("LAZADA_LOCK_STALE_MINUTES", 180, minimum=30)
SUCCESS_INTERVAL_MINUTES = env_int("LAZADA_SUCCESS_INTERVAL_MINUTES", 240, minimum=30)
NO_PRICE_RETRY_MINUTES = env_int("LAZADA_NO_PRICE_RETRY_MINUTES", 720, minimum=30)
ERROR_RETRY_MINUTES = env_int("LAZADA_ERROR_RETRY_MINUTES", 60, minimum=15)
MAX_RETRY_DELAY_MINUTES = env_int("LAZADA_MAX_RETRY_DELAY_MINUTES", 1440, minimum=60)
FINAL_STATUS_FAILURES = env_int("LAZADA_FINAL_STATUS_FAILURES", 3, minimum=2, maximum=10)


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


def scalar(row):
    if row is None:
        return None
    if isinstance(row, dict):
        return next(iter(row.values()))
    return row[0]


def retry_delay_minutes(base_minutes, failure_count):
    return min(int(base_minutes) * max(1, int(failure_count or 0)), MAX_RETRY_DELAY_MINUTES)


def clean_price(raw_price_str):
    try:
        first_price = str(raw_price_str).split("-")[0]
        digits = re.sub(r"\D", "", first_price)
        return int(digits) if digits else None
    except Exception:
        return None


def parse_compact_number(text):
    try:
        match = re.search(r"([\d,.]+)\s*([kKmM]|tr|TR)?", text)
        if not match:
            return 0

        number_text = match.group(1)
        suffix = (match.group(2) or "").lower()
        if suffix:
            value = float(number_text.replace(",", "."))
            if suffix == "k":
                value *= 1_000
            elif suffix in {"m", "tr"}:
                value *= 1_000_000
            return int(value)

        digits = re.sub(r"\D", "", number_text)
        return int(digits) if digits else 0
    except Exception:
        return 0


def create_driver():
    options = uc.ChromeOptions()
    profile_path = get_profile_path(PROFILE_NAME)
    options.add_argument(f"--user-data-dir={profile_path}")
    options.add_argument("--disable-background-timer-throttling")
    options.add_argument("--disable-backgrounding-occluded-windows")
    options.add_argument("--disable-renderer-backgrounding")
    options.add_argument("--disable-notifications")
    options.add_argument("--lang=vi-VN")
    options.add_argument("--window-size=1920,1080")
    options.page_load_strategy = "eager"

    prefs = {
        "profile.managed_default_content_settings.images": 1 if LOAD_IMAGES else 2,
        "profile.default_content_setting_values.notifications": 2,
    }
    options.add_experimental_option("prefs", prefs)

    if HEADLESS_MODE:
        options.add_argument("--headless=new")

    driver = uc.Chrome(options=options, version_main=get_chrome_version_main(147))
    driver.set_page_load_timeout(PAGE_LOAD_TIMEOUT)
    return driver


def safe_get(driver, url):
    try:
        driver.get(url)
    except TimeoutException:
        log("  [!] Trang tải quá lâu. Đã dừng tải và đọc phần nội dung đã nhận được.")
        try:
            driver.execute_script("window.stop();")
        except Exception:
            pass


def get_body_text(driver):
    try:
        return str(driver.execute_script("return document.body ? document.body.innerText : '';") or "")
    except Exception:
        return ""


def detect_captcha_or_login(driver):
    current_url = (driver.current_url or "").lower()
    url_markers = ("login", "captcha", "baxia", "punish", "verify")
    for marker in url_markers:
        if marker in current_url:
            return f"url:{marker}"

    body_text = get_body_text(driver).lower()
    text_markers = (
        "captcha",
        "baxia",
        "slide to verify",
        "xác minh",
        "xac minh",
        "security check",
    )
    for marker in text_markers:
        if marker in body_text:
            return f"text:{marker}"

    selectors = (
        "iframe[src*='captcha']",
        "iframe[src*='baxia']",
        "[class*='captcha']",
        "[id*='captcha']",
        "[class*='baxia']",
        "[id*='baxia']",
    )
    for selector in selectors:
        try:
            elements = driver.find_elements(By.CSS_SELECTOR, selector)
            if any(element.is_displayed() for element in elements):
                return f"selector:{selector}"
        except Exception:
            continue

    return None


def save_debug_artifacts(driver, link_id, reason):
    try:
        DEBUG_DIR.mkdir(parents=True, exist_ok=True)
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        safe_reason = re.sub(r"[^a-zA-Z0-9_-]+", "_", reason)[:48] or "blocked"
        prefix = DEBUG_DIR / f"lazada_link_{link_id}_{safe_reason}_{timestamp}"
        screenshot_path = str(prefix.with_suffix(".png"))
        html_path = prefix.with_suffix(".html")

        try:
            driver.save_screenshot(screenshot_path)
        except Exception:
            screenshot_path = ""

        html_path.write_text(driver.page_source or "", encoding="utf-8", errors="ignore")
        log(f"  [gỡ lỗi] Đã lưu HTML kiểm tra: {html_path}")
        if screenshot_path:
            log(f"  [gỡ lỗi] Đã lưu ảnh màn hình: {screenshot_path}")
    except Exception as exc:
        log(f"  [gỡ lỗi] Không thể lưu file kiểm tra: {exc}")


def wait_for_manual_clear(driver, reason):
    if not sys.stdin.isatty():
        return False

    log("")
    log(f"  [thủ công] Lazada yêu cầu xác minh ({reason}).")
    log("  [thủ công] Hãy xử lý trong cửa sổ Chrome đang mở, sau đó quay lại terminal.")
    input("  Nhấn ENTER khi trang đã vào được bình thường...")
    time.sleep(2)
    return detect_captcha_or_login(driver) is None


def handle_blocked_link(driver, cursor, db, link_id, reason):
    log(f"  [bị chặn] Phát hiện captcha/đăng nhập: {reason}")
    save_debug_artifacts(driver, link_id, reason)

    if wait_for_manual_clear(driver, reason):
        log("  [thủ công] Đã xử lý xác minh. Tiếp tục quét link này.")
        return True

    update_status(cursor, db, link_id, STATUS_CAPTCHA, AVAILABILITY_BLOCKED, f"Captcha hoặc đăng nhập: {reason}")
    log(
        "  [bị chặn] Link đã được đánh dấu status=4. "
        f"Bot sẽ tạm bỏ qua link này trong {CAPTCHA_COOLDOWN_MINUTES} phút."
    )
    return False


def shallow_scroll(driver):
    try:
        current_position = 0
        while current_position < 900:
            current_position += random.randint(150, 260)
            driver.execute_script("window.scrollTo(0, arguments[0]);", current_position)
            time.sleep(random.uniform(0.15, 0.35))
    except Exception:
        pass


def detect_unavailable_status(driver):
    body_text = get_body_text(driver).lower()
    if any(marker in body_text for marker in ("hết hàng", "sold out", "out of stock")):
        return AVAILABILITY_OUT_OF_STOCK, "Sàn báo sản phẩm hết hàng"
    if any(marker in body_text for marker in ("tạm ngừng", "ngừng kinh doanh", "không khả dụng", "unavailable")):
        return AVAILABILITY_TEMPORARILY_UNAVAILABLE, "Sản phẩm tạm ngừng bán hoặc không khả dụng"
    if any(marker in body_text for marker in ("not found", "không tìm thấy", "sản phẩm không tồn tại", "removed", "đã bị xóa")):
        return AVAILABILITY_DISCONTINUED, "Sản phẩm không tồn tại hoặc đã bị xóa"
    return None, None


def fetch_lazada_links(cursor):
    cursor.execute(
        f"""
        SELECT id, product_url
        FROM platform_links
        WHERE platform_name = 'Lazada'
          AND is_active = 1
          AND (blocked_until IS NULL OR blocked_until <= NOW())
          AND (next_check_at IS NULL OR next_check_at <= NOW())
          AND (next_scrape_at IS NULL OR next_scrape_at <= NOW())
        ORDER BY
          CASE WHEN status = 0 THEN 0 WHEN last_scraped_at IS NULL THEN 1 ELSE 2 END,
          next_check_at ASC,
          last_scraped_at ASC
        LIMIT {BATCH_LIMIT}
        """
    )
    return cursor.fetchall()


def get_consecutive_failures(cursor, link_id):
    cursor.execute("SELECT consecutive_failures FROM platform_links WHERE id=%s", (link_id,))
    return int(scalar(cursor.fetchone()) or 0)


def finalize_availability_status(cursor, link_id, requested_status):
    if requested_status not in {AVAILABILITY_DISCONTINUED, AVAILABILITY_TEMPORARILY_UNAVAILABLE}:
        return requested_status
    if get_consecutive_failures(cursor, link_id) + 1 >= FINAL_STATUS_FAILURES:
        return requested_status
    return AVAILABILITY_FETCH_ERROR


def update_status(cursor, db, link_id, status, availability_status=AVAILABILITY_FETCH_ERROR, error_message=None):
    try:
        db.ping(reconnect=True, attempts=3, delay=2)
    except Exception as exc:
        log(f"  [!] Cảnh báo kết nối lại database: {exc}")

    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    final_availability_status = finalize_availability_status(cursor, link_id, availability_status)
    next_failure_count = get_consecutive_failures(cursor, link_id) + 1

    if int(status) == STATUS_CAPTCHA:
        blocked_until = minutes_from_now(CAPTCHA_COOLDOWN_MINUTES)
        next_time = blocked_until
    elif final_availability_status in {AVAILABILITY_OUT_OF_STOCK, AVAILABILITY_DISCONTINUED, AVAILABILITY_TEMPORARILY_UNAVAILABLE}:
        blocked_until = None
        next_time = minutes_from_now(NO_PRICE_RETRY_MINUTES)
    else:
        blocked_until = None
        next_time = minutes_from_now(retry_delay_minutes(ERROR_RETRY_MINUTES, next_failure_count))

    cursor.execute(
        """
        UPDATE platform_links
        SET status=%s,
            availability_status=%s,
            error_message=%s,
            last_scraped_at=%s,
            last_checked_at=%s,
            next_scrape_at=%s,
            next_check_at=%s,
            blocked_until=%s,
            retry_count=retry_count + 1,
            consecutive_failures=consecutive_failures + 1
        WHERE id=%s
        """,
        (
            status,
            final_availability_status,
            sanitize_error_message(error_message),
            now,
            now,
            next_time,
            next_time,
            blocked_until,
            link_id,
        ),
    )
    db.commit()


def save_success(cursor, db, link_id, data):
    try:
        db.ping(reconnect=True, attempts=3, delay=2)
    except Exception as exc:
        log(f"  [!] Cảnh báo kết nối lại database: {exc}")

    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    next_time = minutes_from_now(SUCCESS_INTERVAL_MINUTES)
    cursor.execute(
        """
        UPDATE platform_links
        SET current_price=%s,
            original_price=%s,
            historical_sold=%s,
            rating_average=%s,
            review_count=%s,
            status=%s,
            availability_status=%s,
            error_message=NULL,
            last_scraped_at=%s,
            last_checked_at=%s,
            next_scrape_at=%s,
            next_check_at=%s,
            blocked_until=NULL,
            retry_count=0,
            consecutive_failures=0
        WHERE id=%s
        """,
        (
            data["current_price"],
            data["original_price"],
            data["historical_sold"],
            data["rating_average"],
            data["review_count"],
            STATUS_SUCCESS,
            AVAILABILITY_ACTIVE,
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
    db.commit()


def extract_price_from_text(body_text):
    patterns = (
        r"[₫đĐ]\s*[\d,.]+",
        r"[\d,.]+\s*[₫đĐ]",
    )
    for pattern in patterns:
        for match in re.finditer(pattern, body_text):
            price = clean_price(match.group(0))
            if price and price > 1000:
                return price
    return None


def extract_lazada_data(driver):
    current_price = None
    original_price = None
    historical_sold = 0
    rating_average = 0.0
    review_count = 0

    try:
        WebDriverWait(driver, 8).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, "span[class*='salePrice-amount']"))
        )
    except Exception:
        pass

    sale_elements = driver.find_elements(By.CSS_SELECTOR, "span[class*='salePrice-amount']")
    for element in sale_elements:
        price = clean_price(element.text.strip())
        if price and price > 1000:
            current_price = price
            break

    if not current_price:
        current_price = extract_price_from_text(get_body_text(driver))

    if not current_price:
        return None

    original_elements = driver.find_elements(By.CSS_SELECTOR, "span[class*='originalPrice-amount']")
    for element in original_elements:
        price = clean_price(element.text.strip())
        if price and price > current_price:
            original_price = price
            break

    body_text = get_body_text(driver)
    sold_match = re.search(
        r"(đã bán\s*[\d,.]+(?:\s*(?:k|K|m|M|tr|TR))?|[\d,.]+(?:\s*(?:k|K|m|M|tr|TR))?\s*đã bán)",
        body_text,
        re.IGNORECASE,
    )
    if sold_match:
        historical_sold = parse_compact_number(sold_match.group(0))

    try:
        star_elements = driver.find_elements(By.CSS_SELECTOR, ".container-star-v2-score")
        if star_elements and star_elements[0].text.strip():
            rating_average = float(star_elements[0].text.strip().replace(",", "."))
    except Exception as exc:
        log(f"  [cảnh báo] Không đọc được điểm đánh giá: {exc}")

    try:
        review_elements = driver.find_elements(By.CSS_SELECTOR, ".container-star-v2-count")
        if review_elements:
            digits = re.sub(r"\D", "", review_elements[0].text.strip())
            if digits:
                review_count = int(digits)
    except Exception as exc:
        log(f"  [cảnh báo] Không đọc được số lượt đánh giá: {exc}")

    return {
        "current_price": current_price,
        "original_price": original_price,
        "historical_sold": historical_sold,
        "rating_average": rating_average,
        "review_count": review_count,
    }


def warmup_lazada_home(driver):
    log("[khởi tạo] Đang mở trang chủ Lazada bằng Chrome profile đã cấu hình...")
    safe_get(driver, "https://www.lazada.vn/")
    time.sleep(random.uniform(3, 5))
    reason = detect_captcha_or_login(driver)
    if not reason:
        return True

    log(f"[khởi tạo] Trang chủ Lazada đang bị yêu cầu xác minh: {reason}")
    save_debug_artifacts(driver, "home", reason)
    if wait_for_manual_clear(driver, reason):
        return True
    return False


def run_lazada_crawler():
    db = None
    cursor = None
    exit_code = EXIT_OK

    try:
        log("Bắt đầu chạy bot Lazada")
        log(
            "Cấu hình: "
            f"số link mỗi lượt={BATCH_LIMIT}, kích thước mỗi mẻ={CHUNK_SIZE}, "
            f"timeout={PAGE_LOAD_TIMEOUT} giây, chờ captcha={CAPTCHA_COOLDOWN_MINUTES} phút"
        )

        db = mysql.connector.connect(**get_db_config())
        read_cursor = db.cursor(dictionary=True)
        lazada_links = fetch_lazada_links(read_cursor)
        read_cursor.close()
        cursor = db.cursor()

        log(f"Tìm thấy {len(lazada_links)} link Lazada cần quét.")
        if not lazada_links:
            return EXIT_OK

        chunks = [lazada_links[i : i + CHUNK_SIZE] for i in range(0, len(lazada_links), CHUNK_SIZE)]

        for chunk_index, current_chunk in enumerate(chunks, start=1):
            log("")
            log("=" * 60)
            log(f"Bắt đầu mẻ quét {chunk_index}/{len(chunks)} ({len(current_chunk)} link)")
            log("=" * 60)

            driver = None
            try:
                driver = create_driver()
                if not warmup_lazada_home(driver):
                    exit_code = EXIT_CAPTCHA
                    break

                for index, link in enumerate(current_chunk, start=1):
                    link_id = link["id"]
                    url = link["product_url"]
                    log("-" * 60)
                    log(f"[{index}/{len(current_chunk)}] Đang quét link Lazada ID={link_id}")

                    try:
                        safe_get(driver, url)
                        time.sleep(2.5)

                        reason = detect_captcha_or_login(driver)
                        if reason and not handle_blocked_link(driver, cursor, db, link_id, reason):
                            exit_code = EXIT_CAPTCHA
                            if STOP_ON_CAPTCHA:
                                break
                            continue

                        shallow_scroll(driver)
                        time.sleep(1.5)

                        reason = detect_captcha_or_login(driver)
                        if reason and not handle_blocked_link(driver, cursor, db, link_id, reason):
                            exit_code = EXIT_CAPTCHA
                            if STOP_ON_CAPTCHA:
                                break
                            continue

                        data = extract_lazada_data(driver)
                        if not data:
                            availability_status, unavailable_message = detect_unavailable_status(driver)
                            update_status(
                                cursor,
                                db,
                                link_id,
                                STATUS_NO_PRICE,
                                availability_status or AVAILABILITY_FETCH_ERROR,
                                unavailable_message or "Không trích xuất được giá Lazada hợp lệ",
                            )
                            log("  [bỏ qua] Không trích xuất được giá Lazada hợp lệ.")
                            continue

                        save_success(cursor, db, link_id, data)
                        log(f"  [thành công] Giá hiện tại: {format_vn_number(data['current_price'])} đ")
                        if data["original_price"]:
                            log(f"  [thành công] Giá gốc: {format_vn_number(data['original_price'])} đ")
                        if data["historical_sold"]:
                            log(f"  [thành công] Đã bán: {format_vn_number(data['historical_sold'])}")
                        if data["rating_average"]:
                            log(f"  [thành công] Điểm đánh giá: {data['rating_average']}")
                        if data["review_count"]:
                            log(f"  [thành công] Lượt đánh giá: {format_vn_number(data['review_count'])}")
                        log("  [thành công] Đã lưu dữ liệu vào database.")

                    except WebDriverException as exc:
                        update_status(cursor, db, link_id, STATUS_ERROR, AVAILABILITY_FETCH_ERROR, str(exc))
                        log(f"  [lỗi] Lỗi trình duyệt khi quét link: {exc}")
                    except Exception as exc:
                        update_status(cursor, db, link_id, STATUS_ERROR, AVAILABILITY_FETCH_ERROR, str(exc))
                        log(f"  [lỗi] Lỗi bất ngờ khi quét link: {exc}")

                    if exit_code == EXIT_CAPTCHA and STOP_ON_CAPTCHA:
                        break

                    time.sleep(random.uniform(MIN_DELAY_SECONDS, MAX_DELAY_SECONDS))

            except Exception as exc:
                log(f"[lỗi] Mẻ quét Lazada bị lỗi: {exc}")
            finally:
                if driver:
                    try:
                        driver.quit()
                        log("Đã đóng trình duyệt để giải phóng RAM cho mẻ này.")
                    except Exception:
                        pass

            if exit_code == EXIT_CAPTCHA and STOP_ON_CAPTCHA:
                break

            if chunk_index < len(chunks) and CHUNK_DELAY_SECONDS > 0:
                log(f"Nghỉ {CHUNK_DELAY_SECONDS} giây trước mẻ tiếp theo...")
                time.sleep(CHUNK_DELAY_SECONDS)

        if exit_code == EXIT_OK:
            log("Bot Lazada đã hoàn tất lượt quét.")
        else:
            log("Bot Lazada đã dừng vì cần xác minh captcha/đăng nhập.")
        return exit_code

    except Exception as exc:
        log(f"[lỗi nghiêm trọng] Bot Lazada bị lỗi: {exc}")
        return EXIT_FATAL
    finally:
        if cursor:
            cursor.close()
        if db and db.is_connected():
            db.close()


def run_with_lock():
    with FileLock("lazada_crawler", stale_after_minutes=LOCK_STALE_MINUTES) as acquired:
        if not acquired:
            log("[bỏ qua] Bot Lazada đang chạy ở tiến trình khác, bỏ qua lượt này.")
            return EXIT_OK
        return run_lazada_crawler()


if __name__ == "__main__":
    sys.exit(run_with_lock())
