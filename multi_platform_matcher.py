import undetected_chromedriver as uc
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import mysql.connector
import time, os, random, re, sys, urllib.parse
from thefuzz import fuzz
from datetime import datetime

sys.stdout.reconfigure(encoding='utf-8')

print("🤖 BOT ĐI SĂN ĐA SÀN 2.7 - SYNCHRONIZED WITH WEB_TEST DB")

# ==========================================
# 1. CÔNG CỤ XỬ LÝ DỮ LIỆU
# ==========================================
def parse_number(txt):
    """Chuyển đổi các chuỗi '1.2k', 'Đã bán 500' -> số nguyên 1200, 500"""
    if not txt: return 0
    try:
        match = re.search(r'([\d\.,]+)\s*(k|tr|K)?', txt)
        if not match: return 0
        val = float(match.group(1).replace(',', '.'))
        unit = match.group(2).lower() if match.group(2) else ""
        if unit == 'k': val *= 1000
        elif unit == 'tr': val *= 1000000
        return int(val)
    except: return 0

def clean_price(txt):
    if not txt: return 0
    return int(re.sub(r'[^\d]', '', txt))

# ==========================================
# 2. TƯƠNG TÁC DATABASE (PORT 3307)
# ==========================================
def connect_db():
    return mysql.connector.connect(
        host="127.0.0.1", port=3307, user="root", password="", database="web_test"
    )

def get_target_products(platform):
    """Lấy sản phẩm chưa có link của sàn tương ứng"""
    db = connect_db()
    cursor = db.cursor(dictionary=True)
    # Sử dụng đúng tên cột 'name' và logic LEFT JOIN từ SQL của bạn
    sql = f"""
        SELECT p.id, p.name 
        FROM products p 
        LEFT JOIN platform_links pl ON p.id = pl.product_id AND pl.platform_name = '{platform}'
        WHERE pl.id IS NULL
    """
    cursor.execute(sql)
    res = cursor.fetchall()
    db.close()
    return res

def save_matched_data(p_id, platform, url, price, sold, rating, score):
    """Lưu vào platform_links khớp 100% tên cột: rating_average, historical_sold"""
    try:
        db = connect_db()
        cursor = db.cursor()
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        sql = """
            INSERT INTO platform_links 
            (product_id, platform_name, product_url, current_price, historical_sold, 
             rating_average, match_score, status, last_scraped_at) 
            VALUES (%s, %s, %s, %s, %s, %s, %s, 1, %s)
        """
        cursor.execute(sql, (p_id, platform, url, price, sold, rating, score, now))
        db.commit()
        db.close()
        print(f"  ✅ Đã lưu link {platform} (Score: {score}%)")
    except Exception as e: print(f"  ❌ Lỗi lưu DB: {e}")

# ==========================================
# 3. CHIẾN DỊCH ĐI SĂN
# ==========================================
def start_hunting(driver, platform):
    targets = get_target_products(platform)
    if not targets:
        print(f"🎉 Sàn {platform} đã đầy đủ dữ liệu.")
        return

    print(f"\n📋 Đang săn {len(targets)} sản phẩm cho {platform}...")

    for prod in targets:
        p_id, p_name = prod['id'], prod['name']
        print("-" * 30)
        print(f"🔍 Đang tìm: [{p_name}]")

        query = urllib.parse.quote(p_name)
        url = f"https://www.lazada.vn/catalog/?q={query}" if platform == "Lazada" else f"https://shopee.vn/search?keyword={query}"
        
        driver.get(url)
        time.sleep(random.uniform(4, 6))

        # --- BỘ PHANH TAY CHỐNG CAPTCHA ---
        if "punish" in driver.current_url or "verify" in driver.current_url or "captcha" in driver.page_source.lower():
            print(f"  🛑 CẢNH BÁO: {platform} chặn Captcha!")
            input("  ⌨️ Hãy giải Captcha trên trình duyệt rồi nhấn ENTER tại đây -> ")
            time.sleep(2)

        try:
            # Cuộn chuột để load dữ liệu ẩn
            driver.execute_script("window.scrollBy(0, 400);")
            time.sleep(2)

            # Phân tích kết quả (Lấy Top 5)
            wait = WebDriverWait(driver, 10)
            pattern = ".html" if platform == "Lazada" else "-i."
            items = driver.find_elements(By.XPATH, f"//a[contains(@href, '{pattern}')]")
            
            best_res = {'score': 0, 'url': '', 'name': '', 'price': 0, 'sold': 0, 'rating': 0}

            for item in items[:6]:
                try:
                    raw_title = item.get_attribute("title") or item.text.strip().split('\n')[0]
                    if not raw_title or len(raw_title) < 10: continue

                    score = fuzz.token_set_ratio(p_name.lower(), raw_title.lower())
                    if score > best_res['score']:
                        # Bóc tách thêm Giá và Sold từ text của thẻ hoặc thẻ lân cận
                        card_text = item.text
                        price = clean_price(re.search(r'₫?[\d\.]+', card_text).group()) if "₫" in card_text or platform == "Shopee" else 0
                        sold = parse_number(re.search(r'Đã bán [\d\.,kKtr]+', card_text).group()) if "Đã bán" in card_text else 0
                        
                        best_res.update({
                            'score': score, 'url': item.get_attribute("href").split('?')[0],
                            'name': raw_title, 'price': price, 'sold': sold
                        })
                except: continue

            if best_res['score'] >= 75:
                print(f"  🎯 KHỚP: {best_res['name'][:35]}... ({best_res['score']}%)")
                # Xử lý URL Lazada nếu thiếu https
                final_url = best_res['url']
                if final_url.startswith("//"): final_url = "https:" + final_url
                
                save_matched_data(p_id, platform, final_url, best_res['price'], best_res['sold'], 0, best_res['score'])
            else:
                print(f"  ⚠️ Không tìm thấy sản phẩm đủ tin cậy (Max: {best_res['score']}%)")

        except Exception as e: print(f"  ❌ Lỗi quét trang: {e}")
        time.sleep(random.uniform(3, 5))

# ==========================================
# 4. KHỞI CHẠY
# ==========================================
if __name__ == "__main__":
    options = uc.ChromeOptions()
    base_dir = os.path.dirname(os.path.abspath(__file__))
    options.user_data_dir = os.path.join(base_dir, "shopee_profile")

    driver = None
    try:
        driver = uc.Chrome(options=options, version_main=145)
        print("[*] Đã kết nối Profile. Bắt đầu chiến dịch...")
        
        # Săn lần lượt (Shopee trước, Lazada sau)
        start_hunting(driver, "Shopee")
        start_hunting(driver, "Lazada")

    except Exception as e: print(f"❌ Lỗi hệ thống: {e}")
    finally:
        print("\n🛑 Hoàn tất. Đóng trình duyệt.")
        if driver: driver.quit()