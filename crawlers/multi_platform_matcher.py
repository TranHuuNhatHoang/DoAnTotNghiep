import os
import random
import re
import sys
import time
from pathlib import Path
from urllib.parse import quote_plus

import mysql.connector
import undetected_chromedriver as uc
from selenium.common.exceptions import TimeoutException, WebDriverException
from selenium.webdriver.common.by import By
from thefuzz import fuzz

from app_config import get_chrome_version_main, get_db_config, get_profile_path, load_env
from bot_lock import FileLock


try:
    sys.stdout.reconfigure(encoding="utf-8")
except AttributeError:
    pass


load_env()

EXIT_OK = 0
EXIT_FATAL = 1
EXIT_CAPTCHA = 2

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


MATCHER_HEADLESS = env_bool("MATCHER_HEADLESS", False)
MATCHER_THRESHOLD = env_int("MATCHER_THRESHOLD", 80, minimum=50, maximum=100)
MATCHER_BATCH_LIMIT = env_int("MATCHER_BATCH_LIMIT", 0, minimum=0)
MATCHER_MAX_CARDS = env_int("MATCHER_MAX_CARDS", 8, minimum=1, maximum=30)
MATCHER_PAGE_LOAD_TIMEOUT = env_int("MATCHER_PAGE_LOAD_TIMEOUT", 35, minimum=10, maximum=180)
MATCHER_MIN_DELAY_SECONDS = env_float("MATCHER_MIN_DELAY_SECONDS", 3.0, minimum=1.0)
MATCHER_MAX_DELAY_SECONDS = env_float("MATCHER_MAX_DELAY_SECONDS", 5.0, minimum=MATCHER_MIN_DELAY_SECONDS)
MATCHER_PROFILE_NAME = os.getenv("MATCHER_PROFILE_NAME", "master_profile")
MATCHER_LOCK_STALE_MINUTES = env_int("MATCHER_LOCK_STALE_MINUTES", 180, minimum=30)
MATCHER_STOP_ON_CAPTCHA = env_bool("MATCHER_STOP_ON_CAPTCHA", True)
MATCHER_ALLOW_MANUAL_CLEAR = env_bool("MATCHER_ALLOW_MANUAL_CLEAR", False)
MATCHER_PLATFORMS = [
    item.strip()
    for item in os.getenv("MATCHER_PLATFORMS", "Shopee,Lazada").split(",")
    if item.strip() in {"Shopee", "Lazada"}
]


def log(message):
    print(message, flush=True)


def create_driver():
    options = uc.ChromeOptions()
    profile_path = get_profile_path(MATCHER_PROFILE_NAME)
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

    if MATCHER_HEADLESS:
        options.add_argument("--headless=new")

    driver = uc.Chrome(options=options, version_main=get_chrome_version_main(147))
    driver.set_page_load_timeout(MATCHER_PAGE_LOAD_TIMEOUT)
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


def detect_captcha_or_login(driver, platform):
    current_url = (driver.current_url or "").lower()
    common_url_markers = ("login", "captcha", "verify", "buyer/login", "account/login", "/login")
    platform_markers = ("baxia", "punish") if platform == "Lazada" else ()
    for marker in common_url_markers + platform_markers:
        if marker in current_url:
            return f"url:{marker}"

    body_text = get_body_text(driver).lower()
    text_markers = (
        "captcha",
        "slide to verify",
        "drag the slider",
        "please verify",
        "security verification",
        "unusual traffic",
        "access denied",
        "are you a robot",
        "not a robot",
        "i am not a robot",
        "robot check",
        "prove you are human",
        "xác minh",
        "xac minh",
        "security check",
        "baxia",
    )
    for marker in text_markers:
        if marker in body_text:
            return f"text:{marker}"

    selectors = (
        "iframe[src*='captcha']",
        "iframe[src*='verify']",
        "iframe[src*='baxia']",
        "[class*='captcha']",
        "[id*='captcha']",
        "[class*='verify']",
        "[id*='verify']",
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


def save_debug_artifacts(driver, platform, product_id, reason):
    try:
        DEBUG_DIR.mkdir(parents=True, exist_ok=True)
        timestamp = time.strftime("%Y%m%d_%H%M%S")
        safe_reason = re.sub(r"[^a-zA-Z0-9_-]+", "_", reason)[:48] or "blocked"
        prefix = DEBUG_DIR / f"matcher_{platform.lower()}_{product_id}_{safe_reason}_{timestamp}"
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


def wait_for_manual_clear(driver, platform, reason):
    if not MATCHER_ALLOW_MANUAL_CLEAR:
        return False

    if not sys.stdin.isatty():
        return False

    log("")
    log(f"  [thủ công] {platform} yêu cầu xác minh ({reason}).")
    log("  [thủ công] Hãy xử lý trong cửa sổ Chrome đang mở, sau đó quay lại terminal.")
    input("  Nhấn ENTER khi trang đã vào được bình thường...")
    time.sleep(2)
    return detect_captcha_or_login(driver, platform) is None


def get_products_missing_platform(conn, platform_name):
    sql = """
        SELECT p.id, p.name
        FROM products p
        LEFT JOIN platform_links pl
          ON p.id = pl.product_id AND pl.platform_name = %s
        WHERE pl.id IS NULL
        ORDER BY p.id DESC
    """
    if MATCHER_BATCH_LIMIT > 0:
        sql += f" LIMIT {MATCHER_BATCH_LIMIT}"

    cursor = conn.cursor(dictionary=True)
    cursor.execute(sql, (platform_name,))
    products = cursor.fetchall()
    cursor.close()
    return products


def save_matched_link(conn, product_id, platform_name, matched_url, match_score):
    cursor = conn.cursor()
    try:
        cursor.execute(
            """
            INSERT INTO platform_links
                (product_id, platform_name, product_url, current_price, status, is_active, match_score)
            VALUES (%s, %s, %s, 0, 0, 1, %s)
            """,
            (product_id, platform_name, matched_url, match_score),
        )
        conn.commit()
        log(f"  [thành công] Đã gắn link {platform_name} vào database. Điểm khớp: {match_score}/100.")
        return True
    except Exception as exc:
        conn.rollback()
        log(f"  [lỗi] Không lưu được link {platform_name}: {exc}")
        return False
    finally:
        cursor.close()


def platform_search_config(platform, product_name):
    encoded_name = quote_plus(product_name)
    if platform == "Shopee":
        return {
            "url": f"https://shopee.vn/search?keyword={encoded_name}",
            "xpath": "//a[contains(@href, '-i.')]",
        }
    if platform == "Lazada":
        return {
            "url": f"https://www.lazada.vn/catalog/?q={encoded_name}",
            "xpath": "//a[contains(@href, '.html')]",
        }
    raise ValueError(f"Sàn không hỗ trợ: {platform}")


def extract_candidate_name(card):
    text_lines = [line.strip() for line in card.text.strip().split("\n") if line.strip()]
    if not text_lines:
        return ""
    return max(text_lines, key=len)


def clean_product_url(url):
    if not url:
        return ""
    return url.split("?")[0]


def find_best_match(driver, platform, product):
    product_name = product["name"]
    config = platform_search_config(platform, product_name)

    safe_get(driver, config["url"])
    time.sleep(2.5)

    reason = detect_captcha_or_login(driver, platform)
    if reason:
        save_debug_artifacts(driver, platform, product["id"], reason)
        if wait_for_manual_clear(driver, platform, reason):
            log("  [thủ công] Đã xử lý xác minh. Tiếp tục tìm link.")
        else:
            return {"blocked": True, "reason": reason}

    driver.execute_script("window.scrollBy(0, 650);")
    time.sleep(2)

    reason = detect_captcha_or_login(driver, platform)
    if reason:
        save_debug_artifacts(driver, platform, product["id"], reason)
        if wait_for_manual_clear(driver, platform, reason):
            log("  [thá»§ cĂ´ng] ÄĂ£ xá»­ lĂ½ xĂ¡c minh. Tiáº¿p tá»¥c tĂ¬m link.")
        else:
            return {"blocked": True, "reason": reason}

    cards = driver.find_elements(By.XPATH, config["xpath"])
    best_match_url = ""
    best_match_score = 0
    best_match_name = ""

    for card in cards[:MATCHER_MAX_CARDS]:
        extracted_name = extract_candidate_name(card)
        extracted_url = clean_product_url(card.get_attribute("href"))

        if not extracted_name or not extracted_url:
            continue

        score = fuzz.token_set_ratio(product_name.lower(), extracted_name.lower())
        log(f"  [ứng viên] {extracted_name[:60]}... Điểm: {score}/100")

        if score > best_match_score:
            best_match_score = score
            best_match_url = extracted_url
            best_match_name = extracted_name

    return {
        "blocked": False,
        "url": best_match_url,
        "score": best_match_score,
        "name": best_match_name,
    }


def run_matcher():
    if not MATCHER_PLATFORMS:
        log("[lỗi] Không có sàn nào được cấu hình trong MATCHER_PLATFORMS.")
        return EXIT_FATAL

    conn = None
    driver = None
    exit_code = EXIT_OK

    try:
        log("Bắt đầu chạy bot tìm link đa sàn")
        log(
            "Cấu hình: "
            f"sàn={', '.join(MATCHER_PLATFORMS)}, ngưỡng khớp={MATCHER_THRESHOLD}/100, "
            f"số ứng viên mỗi sản phẩm={MATCHER_MAX_CARDS}"
        )

        log(
            "Captcha mode: "
            f"headless={MATCHER_HEADLESS}, stop_on_captcha={MATCHER_STOP_ON_CAPTCHA}, "
            f"allow_manual_clear={MATCHER_ALLOW_MANUAL_CLEAR}"
        )

        conn = mysql.connector.connect(**get_db_config())
        driver = create_driver()

        for platform in MATCHER_PLATFORMS:
            log("")
            log("=" * 60)
            log(f"Bắt đầu tìm link cho sàn {platform}")
            log("=" * 60)

            target_products = get_products_missing_platform(conn, platform)
            if not target_products:
                log(f"Tất cả sản phẩm hiện đã có link {platform}.")
                continue

            log(f"Tìm thấy {len(target_products)} sản phẩm cần tìm link {platform}.")

            for index, product in enumerate(target_products, start=1):
                log("-" * 60)
                log(f"[{index}/{len(target_products)}] Đang tìm [{platform}] cho: {product['name']}")

                try:
                    result = find_best_match(driver, platform, product)
                    if result.get("blocked"):
                        log(f"  [bị chặn] {platform} yêu cầu captcha/đăng nhập: {result['reason']}")
                        exit_code = EXIT_CAPTCHA
                        if MATCHER_STOP_ON_CAPTCHA:
                            break
                        continue

                    if result["score"] >= MATCHER_THRESHOLD and result["url"]:
                        log(f"  [khớp] {result['name'][:70]}...")
                        save_matched_link(conn, product["id"], platform, result["url"], result["score"])
                    else:
                        log(
                            "  [bỏ qua] Điểm khớp cao nhất "
                            f"chỉ đạt {result['score']}/100, chưa đủ ngưỡng để tự gắn link."
                        )
                except WebDriverException as exc:
                    log(f"  [lỗi] Lỗi trình duyệt khi tìm link {platform}: {exc}")
                except Exception as exc:
                    log(f"  [lỗi] Lỗi khi đọc kết quả tìm kiếm {platform}: {exc}")

                time.sleep(random.uniform(MATCHER_MIN_DELAY_SECONDS, MATCHER_MAX_DELAY_SECONDS))

            if exit_code == EXIT_CAPTCHA and MATCHER_STOP_ON_CAPTCHA:
                break

        if exit_code == EXIT_OK:
            log("Bot tìm link đa sàn đã hoàn tất.")
        else:
            log("Bot tìm link đa sàn đã dừng vì cần xác minh captcha/đăng nhập.")
        return exit_code

    except Exception as exc:
        log(f"[lỗi nghiêm trọng] Bot tìm link đa sàn bị lỗi: {exc}")
        return EXIT_FATAL
    finally:
        if driver:
            try:
                driver.quit()
                log("Đã đóng trình duyệt.")
            except Exception:
                pass
        if conn and conn.is_connected():
            conn.close()


def run_with_lock():
    with FileLock("multi_platform_matcher", stale_after_minutes=MATCHER_LOCK_STALE_MINUTES) as acquired:
        if not acquired:
            log("[bỏ qua] Bot tìm link đang chạy ở tiến trình khác, bỏ qua lượt này.")
            return EXIT_OK
        return run_matcher()


if __name__ == "__main__":
    sys.exit(run_with_lock())
