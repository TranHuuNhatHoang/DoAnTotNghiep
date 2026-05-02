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

from app_config import get_chrome_version_main, get_db_config, get_profile_path, load_env
from bot_lock import FileLock


try:
    sys.stdout.reconfigure(encoding="utf-8")
except AttributeError:
    pass


load_env()

VND_SYMBOL = "\u20ab"
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

PROJECT_ROOT = Path(__file__).resolve().parent.parent
DEBUG_DIR = PROJECT_ROOT / "storage" / "bot_debug"


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


HEADLESS_MODE = env_bool("SHOPEE_HEADLESS", False)
BATCH_LIMIT = env_int("SHOPEE_BATCH_LIMIT", 8, minimum=1, maximum=30)
CAPTCHA_COOLDOWN_MINUTES = env_int("SHOPEE_CAPTCHA_COOLDOWN_MINUTES", 180, minimum=15)
STOP_ON_CAPTCHA = env_bool("SHOPEE_STOP_ON_CAPTCHA", True)
ALLOW_MANUAL_CLEAR = env_bool("SHOPEE_ALLOW_MANUAL_CLEAR", False)
PAGE_LOAD_TIMEOUT = env_int("SHOPEE_PAGE_LOAD_TIMEOUT", 35, minimum=10, maximum=180)
MIN_DELAY_SECONDS = env_float("SHOPEE_MIN_DELAY_SECONDS", 6.0, minimum=1.0)
MAX_DELAY_SECONDS = env_float("SHOPEE_MAX_DELAY_SECONDS", 12.0, minimum=MIN_DELAY_SECONDS)
WARMUP_SECONDS = env_float("SHOPEE_WARMUP_SECONDS", 5.0, minimum=1.0)
PROFILE_NAME = os.getenv("SHOPEE_PROFILE_NAME", "master_profile")
SUCCESS_INTERVAL_MINUTES = env_int("SHOPEE_SUCCESS_INTERVAL_MINUTES", 240, minimum=30)
NO_PRICE_RETRY_MINUTES = env_int("SHOPEE_NO_PRICE_RETRY_MINUTES", 720, minimum=30)
ERROR_RETRY_MINUTES = env_int("SHOPEE_ERROR_RETRY_MINUTES", 60, minimum=15)
MAX_RETRY_DELAY_MINUTES = env_int("SHOPEE_MAX_RETRY_DELAY_MINUTES", 1440, minimum=60)
LOCK_STALE_MINUTES = env_int("SHOPEE_LOCK_STALE_MINUTES", 120, minimum=30)
FINAL_STATUS_FAILURES = env_int("SHOPEE_FINAL_STATUS_FAILURES", 3, minimum=2, maximum=10)


def log(message):
    print(message, flush=True)


def format_vn_number(value):
    try:
        return f"{int(value):,}".replace(",", ".")
    except (TypeError, ValueError):
        return str(value)


SCRAPE_QUEUE_COLUMNS = {
    "next_scrape_at": "DATETIME NULL AFTER last_scraped_at",
    "last_checked_at": "DATETIME NULL AFTER last_scraped_at",
    "next_check_at": "DATETIME NULL AFTER next_scrape_at",
    "blocked_until": "DATETIME NULL AFTER next_scrape_at",
    "retry_count": "INT NOT NULL DEFAULT 0 AFTER blocked_until",
    "consecutive_failures": "INT NOT NULL DEFAULT 0 AFTER retry_count",
    "scrape_priority": "TINYINT NOT NULL DEFAULT 5 AFTER retry_count",
    "availability_status": "ENUM('unknown', 'active', 'out_of_stock', 'temporarily_unavailable', 'discontinued', 'invalid_url', 'fetch_error', 'blocked_or_captcha') NOT NULL DEFAULT 'unknown' AFTER status",
    "error_message": "VARCHAR(500) NULL AFTER availability_status",
}


def scalar(row):
    if row is None:
        return None
    if isinstance(row, dict):
        return next(iter(row.values()))
    return row[0]


def ensure_scrape_queue_schema(cursor, db):
    for column_name, column_definition in SCRAPE_QUEUE_COLUMNS.items():
        cursor.execute(
            """
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'platform_links'
              AND COLUMN_NAME = %s
            """,
            (column_name,),
        )
        if int(scalar(cursor.fetchone()) or 0) == 0:
            cursor.execute(f"ALTER TABLE platform_links ADD COLUMN {column_name} {column_definition}")

    cursor.execute(
        """
        SELECT COUNT(*)
        FROM INFORMATION_SCHEMA.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'platform_links'
          AND INDEX_NAME = 'idx_platform_scrape_queue'
        """
    )
    if int(scalar(cursor.fetchone()) or 0) == 0:
        cursor.execute(
            """
            CREATE INDEX idx_platform_scrape_queue
            ON platform_links
                (platform_name, is_active, blocked_until, next_scrape_at, scrape_priority, last_scraped_at)
            """
        )

    db.commit()


def minutes_from_now(minutes):
    return (datetime.now() + timedelta(minutes=int(minutes))).strftime("%Y-%m-%d %H:%M:%S")


def sanitize_error_message(message):
    text = re.sub(r"\s+", " ", str(message or "")).strip()
    if not text:
        return None
    return text[:500]


def get_retry_count(cursor, link_id):
    cursor.execute("SELECT retry_count FROM platform_links WHERE id=%s", (link_id,))
    return int(scalar(cursor.fetchone()) or 0)


def get_consecutive_failures(cursor, link_id):
    cursor.execute("SELECT consecutive_failures FROM platform_links WHERE id=%s", (link_id,))
    return int(scalar(cursor.fetchone()) or 0)


def retry_delay_minutes(base_minutes, retry_count):
    return min(int(base_minutes) * max(1, retry_count + 1), MAX_RETRY_DELAY_MINUTES)


def finalize_availability_status(cursor, link_id, requested_status):
    if requested_status not in {AVAILABILITY_DISCONTINUED, AVAILABILITY_TEMPORARILY_UNAVAILABLE}:
        return requested_status
    if get_consecutive_failures(cursor, link_id) + 1 >= FINAL_STATUS_FAILURES:
        return requested_status
    return AVAILABILITY_FETCH_ERROR


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


def build_driver():
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
        "profile.managed_default_content_settings.images": 1,
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


def has_visible_selector(driver, selector):
    try:
        elements = driver.find_elements(By.CSS_SELECTOR, selector)
        return any(element.is_displayed() for element in elements)
    except Exception:
        return False


def detect_captcha_or_login(driver):
    current_url = (driver.current_url or "").lower()
    url_markers = ("captcha", "verify", "buyer/login", "account/login", "/login")
    for marker in url_markers:
        if marker in current_url:
            return f"url:{marker}"

    body_text = get_body_text(driver).lower()
    text_markers = (
        "captcha",
        "slide to verify",
        "\u0111\u1ec3 x\u00e1c minh",
        "x\u00e1c minh",
        "xac minh",
        "keo thanh truot",
    )
    for marker in text_markers:
        if marker in body_text:
            return f"text:{marker}"

    selectors = (
        "iframe[src*='captcha']",
        "iframe[src*='verify']",
        "[class*='captcha']",
        "[id*='captcha']",
    )
    for selector in selectors:
        if has_visible_selector(driver, selector):
            return f"selector:{selector}"

    return None


def detect_unavailable_status(driver):
    body_text = get_body_text(driver).lower()
    if any(marker in body_text for marker in ("sold out", "out of stock", "hết hàng", "het hang")):
        return AVAILABILITY_OUT_OF_STOCK, "Sàn báo sản phẩm hết hàng"
    if any(marker in body_text for marker in ("unavailable", "tạm ngừng", "tam ngung", "ngừng bán", "ngung ban")):
        return AVAILABILITY_TEMPORARILY_UNAVAILABLE, "Sản phẩm tạm ngừng bán hoặc không khả dụng"
    if any(
        marker in body_text
        for marker in (
            "not found",
            "product not found",
            "sản phẩm không tồn tại",
            "san pham khong ton tai",
            "không tìm thấy",
            "khong tim thay",
            "đã bị xóa",
            "da bi xoa",
        )
    ):
        return AVAILABILITY_DISCONTINUED, "Sản phẩm không tồn tại hoặc đã bị xóa"
    return None, None


def save_debug_artifacts(driver, link_id, reason):
    try:
        DEBUG_DIR.mkdir(parents=True, exist_ok=True)
        timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
        safe_reason = re.sub(r"[^a-zA-Z0-9_-]+", "_", reason)[:48] or "blocked"
        prefix = DEBUG_DIR / f"shopee_link_{link_id}_{safe_reason}_{timestamp}"

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
    if not ALLOW_MANUAL_CLEAR:
        return False

    if not sys.stdin.isatty():
        return False

    log("")
    log(f"  [thủ công] Shopee yêu cầu xác minh ({reason}).")
    log("  [thủ công] Hãy xử lý trong cửa sổ Chrome đang mở, sau đó quay lại terminal.")
    input("  Nhấn ENTER khi trang đã vào được bình thường...")
    time.sleep(2)
    return detect_captcha_or_login(driver) is None


def update_status(cursor, db, link_id, status, touch_time=True, availability_status=AVAILABILITY_FETCH_ERROR, error_message=None):
    try:
        db.ping(reconnect=True, attempts=3, delay=2)
    except Exception as exc:
        log(f"  [!] Cảnh báo kết nối lại database: {exc}")

    if touch_time:
        now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        retry_count = get_retry_count(cursor, link_id)
        final_availability_status = finalize_availability_status(cursor, link_id, availability_status)

        if int(status) == STATUS_CAPTCHA:
            blocked_until = minutes_from_now(CAPTCHA_COOLDOWN_MINUTES)
            next_scrape_at = blocked_until
            next_message = f"tạm nghỉ đến {next_scrape_at}"
        elif final_availability_status in {AVAILABILITY_OUT_OF_STOCK, AVAILABILITY_DISCONTINUED, AVAILABILITY_TEMPORARILY_UNAVAILABLE}:
            blocked_until = None
            next_scrape_at = minutes_from_now(NO_PRICE_RETRY_MINUTES)
            next_message = f"quét lại sau {NO_PRICE_RETRY_MINUTES} phút"
        elif int(status) == STATUS_ERROR:
            blocked_until = None
            delay_minutes = retry_delay_minutes(ERROR_RETRY_MINUTES, retry_count)
            next_scrape_at = minutes_from_now(delay_minutes)
            next_message = f"quét lại sau {delay_minutes} phút"
        else:
            blocked_until = None
            next_scrape_at = minutes_from_now(ERROR_RETRY_MINUTES)
            next_message = f"quét lại sau {ERROR_RETRY_MINUTES} phút"

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
                next_scrape_at,
                next_scrape_at,
                blocked_until,
                link_id,
            ),
        )
        log(f"  [lịch quét] Link ID={link_id} sẽ {next_message}.")
    else:
        cursor.execute("UPDATE platform_links SET status=%s WHERE id=%s", (status, link_id))
    db.commit()


def save_success(cursor, db, link_id, data):
    try:
        db.ping(reconnect=True, attempts=3, delay=2)
    except Exception as exc:
        log(f"  [!] Cảnh báo kết nối lại database: {exc}")

    now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    next_scrape_at = minutes_from_now(SUCCESS_INTERVAL_MINUTES)
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
            next_scrape_at,
            next_scrape_at,
            link_id,
        ),
    )
    cursor.execute(
        "INSERT INTO price_history (link_id, price, scraped_at) VALUES (%s, %s, %s)",
        (link_id, data["current_price"], now),
    )
    db.commit()


def shallow_scroll(driver):
    try:
        current_position = 0
        max_position = random.randint(900, 1500)
        while current_position < max_position:
            current_position += random.randint(180, 320)
            driver.execute_script("window.scrollTo(0, arguments[0]);", current_position)
            time.sleep(random.uniform(0.25, 0.55))
    except Exception:
        pass


def wait_for_price_or_block(driver, timeout=12):
    deadline = time.time() + timeout
    while time.time() < deadline:
        blocked = detect_captcha_or_login(driver)
        if blocked:
            return False, blocked

        if VND_SYMBOL in get_body_text(driver):
            return True, None

        time.sleep(0.7)
    return False, None


def get_text_with_parent_fallback(element):
    try:
        text = element.text.strip()
        font_size = element.value_of_css_property("font-size")
        if len(text) <= 2:
            parent = element.find_element(By.XPATH, "..")
            text = parent.text.strip()
            font_size = parent.value_of_css_property("font-size")
        return text, font_size
    except Exception:
        return "", "0px"


def extract_price_candidates_from_dom(driver):
    candidates = []
    try:
        elements = driver.find_elements(By.XPATH, f"//*[contains(text(), '{VND_SYMBOL}')]")
    except Exception:
        elements = []

    for element in elements:
        try:
            if not element.is_displayed():
                continue

            text, font_size_text = get_text_with_parent_fallback(element)
            if not text or len(text) > 80:
                continue

            price = clean_price(text)
            if not price or price <= 1000:
                continue

            font_size = float(str(font_size_text).replace("px", "").strip() or 0)
            candidates.append({"price": price, "text": text, "font_size": font_size})
        except Exception:
            continue

    return candidates


def extract_price_candidates_from_text(body_text):
    candidates = []
    price_pattern = re.compile(
        rf"{re.escape(VND_SYMBOL)}\s*[\d.,]+(?:\s*-\s*{re.escape(VND_SYMBOL)}?\s*[\d.,]+)?"
    )
    for match in price_pattern.finditer(body_text):
        text = match.group(0)
        price = clean_price(text)
        if price and price > 1000:
            candidates.append({"price": price, "text": text, "font_size": 0})
    return candidates


def extract_product_data(driver):
    body_text = get_body_text(driver)
    candidates = extract_price_candidates_from_dom(driver)
    if not candidates:
        candidates = extract_price_candidates_from_text(body_text)

    if not candidates:
        return None

    best = sorted(candidates, key=lambda item: (item["font_size"], -item["price"]), reverse=True)[0]
    current_price = best["price"]
    higher_prices = sorted({item["price"] for item in candidates if item["price"] > current_price})
    original_price = higher_prices[0] if higher_prices else None

    sold = 0
    sold_match = re.search(
        r"([\d,.]+(?:\s*(?:k|K|m|M|tr|TR))?)\s*(?:\u0111\u00e3 b\u00e1n|sold)",
        body_text,
        re.IGNORECASE,
    )
    if sold_match:
        sold = parse_compact_number(sold_match.group(0))

    reviews = 0
    review_match = re.search(
        r"([\d,.]+(?:\s*(?:k|K|m|M|tr|TR))?)\s*(?:\u0111\u00e1nh gi\u00e1|reviews?)",
        body_text,
        re.IGNORECASE,
    )
    if review_match:
        reviews = parse_compact_number(review_match.group(0))

    rating = 0.0
    rating_match = re.search(
        r"\b([0-5](?:[.,]\d)?)\b.{0,40}(?:\u0111\u00e1nh gi\u00e1|reviews?)",
        body_text,
        re.IGNORECASE | re.DOTALL,
    )
    if rating_match:
        try:
            rating = float(rating_match.group(1).replace(",", "."))
        except ValueError:
            rating = 0.0

    return {
        "current_price": current_price,
        "original_price": original_price,
        "historical_sold": sold,
        "rating_average": rating,
        "review_count": reviews,
        "source_text": best["text"],
    }


def fetch_shopee_links(cursor):
    limit = int(BATCH_LIMIT)
    cursor.execute(
        """
        SELECT id, product_url
        FROM platform_links
        WHERE platform_name = 'Shopee'
          AND is_active = 1
          AND (blocked_until IS NULL OR blocked_until <= NOW())
          AND (next_check_at IS NULL OR next_check_at <= NOW())
          AND (next_scrape_at IS NULL OR next_scrape_at <= NOW())
        ORDER BY
          CASE
            WHEN status = 0 THEN 0
            WHEN last_scraped_at IS NULL THEN 1
            WHEN status IN (2, 3) THEN 2
            ELSE 3
          END,
          scrape_priority ASC,
          next_scrape_at ASC,
          last_scraped_at ASC
        LIMIT %s
        """,
        (limit,),
    )
    return cursor.fetchall()


def handle_blocked_link(driver, cursor, db, link_id, reason):
    log(f"  [bị chặn] Phát hiện captcha/đăng nhập: {reason}")
    save_debug_artifacts(driver, link_id, reason)

    if wait_for_manual_clear(driver, reason):
        log("  [thủ công] Đã xử lý xác minh. Tiếp tục quét link này.")
        return True

    update_status(cursor, db, link_id, STATUS_CAPTCHA, availability_status=AVAILABILITY_BLOCKED, error_message=f"Captcha hoặc đăng nhập: {reason}")
    log(
        "  [bị chặn] Link đã được đánh dấu status=4. "
        f"Bot sẽ tạm bỏ qua link này trong {CAPTCHA_COOLDOWN_MINUTES} phút."
    )
    return False


def warmup_shopee_home(driver):
    log("[khởi tạo] Đang mở trang chủ Shopee bằng Chrome profile đã cấu hình...")
    safe_get(driver, "https://shopee.vn/")
    time.sleep(WARMUP_SECONDS)
    reason = detect_captcha_or_login(driver)
    if not reason:
        return True

    log(f"[khởi tạo] Trang chủ Shopee đang bị yêu cầu xác minh: {reason}")
    save_debug_artifacts(driver, "home", reason)
    if wait_for_manual_clear(driver, reason):
        return True
    return False


def main():
    log("Bắt đầu chạy bot Shopee")
    log(
        "Cấu hình: "
        f"số link mỗi lượt={BATCH_LIMIT}, thời gian chờ captcha={CAPTCHA_COOLDOWN_MINUTES} phút, "
        f"chu kỳ quét lại sau thành công={SUCCESS_INTERVAL_MINUTES} phút, "
        f"chạy ẩn trình duyệt={HEADLESS_MODE}, dừng khi gặp captcha={STOP_ON_CAPTCHA}"
    )

    db = None
    cursor = None
    driver = None
    exit_code = EXIT_OK

    try:
        db = mysql.connector.connect(**get_db_config())
        read_cursor = db.cursor(dictionary=True)
        ensure_scrape_queue_schema(read_cursor, db)
        shopee_links = fetch_shopee_links(read_cursor)
        read_cursor.close()
        cursor = db.cursor()

        log(f"Tìm thấy {len(shopee_links)} link Shopee cần quét.")
        if not shopee_links:
            return EXIT_OK

        driver = build_driver()
        if not warmup_shopee_home(driver):
            log("[bị chặn] Dừng trước khi quét vì trang chủ Shopee yêu cầu xác minh.")
            return EXIT_CAPTCHA

        for index, link in enumerate(shopee_links, start=1):
            link_id = link["id"]
            url = link["product_url"]
            log("-" * 60)
            log(f"[{index}/{len(shopee_links)}] Đang quét link Shopee ID={link_id}")

            try:
                safe_get(driver, url)
                time.sleep(random.uniform(2.0, 4.0))

                reason = detect_captcha_or_login(driver)
                if reason and not handle_blocked_link(driver, cursor, db, link_id, reason):
                    exit_code = EXIT_CAPTCHA
                    if STOP_ON_CAPTCHA:
                        break
                    continue

                shallow_scroll(driver)

                price_ready, blocked_reason = wait_for_price_or_block(driver, timeout=12)
                if blocked_reason and not handle_blocked_link(driver, cursor, db, link_id, blocked_reason):
                    exit_code = EXIT_CAPTCHA
                    if STOP_ON_CAPTCHA:
                        break
                    continue

                unavailable_status, unavailable_message = detect_unavailable_status(driver)
                if not price_ready and unavailable_status:
                    log("  [bỏ qua] Sản phẩm có vẻ đã ngừng bán hoặc bị xóa.")
                    update_status(
                        cursor,
                        db,
                        link_id,
                        STATUS_NO_PRICE,
                        availability_status=unavailable_status,
                        error_message=unavailable_message,
                    )
                    continue

                product_data = extract_product_data(driver)
                if not product_data:
                    log("  [bỏ qua] Không trích xuất được giá Shopee hợp lệ.")
                    update_status(
                        cursor,
                        db,
                        link_id,
                        STATUS_NO_PRICE,
                        availability_status=AVAILABILITY_FETCH_ERROR,
                        error_message="Không trích xuất được giá Shopee hợp lệ",
                    )
                    continue

                save_success(cursor, db, link_id, product_data)
                log(f"  [thành công] Giá hiện tại: {format_vn_number(product_data['current_price'])} đ")
                if product_data["original_price"]:
                    log(f"  [thành công] Giá gốc: {format_vn_number(product_data['original_price'])} đ")
                if product_data["historical_sold"]:
                    log(f"  [thành công] Đã bán: {format_vn_number(product_data['historical_sold'])}")
                if product_data["rating_average"]:
                    log(f"  [thành công] Điểm đánh giá: {product_data['rating_average']}")
                log("  [thành công] Đã lưu dữ liệu vào database.")

            except WebDriverException as exc:
                log(f"  [lỗi] Lỗi trình duyệt: {exc}")
                update_status(cursor, db, link_id, STATUS_ERROR, availability_status=AVAILABILITY_FETCH_ERROR, error_message=str(exc))
            except Exception as exc:
                log(f"  [lỗi] Lỗi bất ngờ khi quét link: {exc}")
                update_status(cursor, db, link_id, STATUS_ERROR, availability_status=AVAILABILITY_FETCH_ERROR, error_message=str(exc))

            time.sleep(random.uniform(MIN_DELAY_SECONDS, MAX_DELAY_SECONDS))

        if exit_code == EXIT_OK:
            log("Bot Shopee đã hoàn tất lượt quét.")
        else:
            log("Bot Shopee đã dừng vì cần xác minh captcha/đăng nhập.")
        return exit_code

    except Exception as exc:
        log(f"[lỗi nghiêm trọng] Bot Shopee bị lỗi: {exc}")
        return EXIT_FATAL
    finally:
        if driver:
            try:
                driver.quit()
            except Exception:
                pass
        if cursor:
            try:
                cursor.close()
            except Exception:
                pass
        if db and db.is_connected():
            db.close()


def run_with_lock():
    with FileLock("shopee_crawler", stale_after_minutes=LOCK_STALE_MINUTES) as acquired:
        if not acquired:
            log("[bỏ qua] Bot Shopee đang chạy ở tiến trình khác, bỏ qua lượt này.")
            return EXIT_OK
        return main()


if __name__ == "__main__":
    sys.exit(run_with_lock())
