import undetected_chromedriver as uc
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import mysql.connector
import time, os, random, re, sys
from datetime import datetime

# Cấu hình hiển thị tiếng Việt
sys.stdout.reconfigure(encoding='utf-8')

print("🚀 KHỞI ĐỘNG BOT LAZADA 2.8 - FULL DATA EXTRACTION")

# ==========================================
# 1. CẤU HÌNH HỆ THỐNG
# ==========================================
BATCH_LIMIT = 10 
DB_CONFIG = {
    "host": "127.0.0.1",
    "port": 3307,
    "user": "root",
    "password": "",
    "database": "web_test"
}

# ==========================================
# 2. CÁC HÀM TIỆN ÍCH
# ==========================================
def parse_number(txt):
    """Chuyển đổi '1.2k' -> 1200, 'Đã bán 500' -> 500"""
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

def update_db(link_id, data):
    """Cập nhật dữ liệu khớp 100% với schema platform_links"""
    try:
        db = mysql.connector.connect(**DB_CONFIG)
        cursor = db.cursor()
        now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
        
        # Cập nhật bảng platform_links
        sql = """
            UPDATE platform_links 
            SET current_price = %s, original_price = %s, historical_sold = %s, 
                rating_average = %s, review_count = %s, status = 1, last_scraped_at = %s 
            WHERE id = %s
        """
        cursor.execute(sql, (
            data['price'], data['orig_price'], data['sold'], 
            data['rating'], data['reviews'], now, link_id
        ))
        
        # Lưu lịch sử giá nếu có giá hợp lệ
        if data['price'] > 0:
            cursor.execute("INSERT INTO price_history (link_id, price, scraped_at) VALUES (%s, %s, %s)", 
                           (link_id, data['price'], now))
            
        db.commit()
        db.close()
        print(f"  [+] Đã lưu Database thành công (Link ID: {link_id})")
    except Exception as e:
        print(f"  [!] Lỗi DB: {e}")

# ==========================================
# 3. LUỒNG CHẠY CHÍNH
# ==========================================
if __name__ == "__main__":
    # Lấy danh sách link Lazada cần cào
    try:
        db_init = mysql.connector.connect(**DB_CONFIG)
        cursor = db_init.cursor(dictionary=True)
        cursor.execute(f"SELECT id, product_url FROM platform_links WHERE platform_name = 'Lazada' AND is_active = 1 ORDER BY last_scraped_at ASC LIMIT {BATCH_LIMIT}")
        links = cursor.fetchall()
        db_init.close()
    except Exception as e:
        print(f"❌ Lỗi kết nối: {e}"); exit()

    if not links: print("🎉 Hết link cần quét."); exit()

    options = uc.ChromeOptions()
    base_dir = os.path.dirname(os.path.abspath(__file__))
    options.user_data_dir = os.path.join(base_dir, "shopee_profile")

    driver = None
    try:
        driver = uc.Chrome(options=options, version_main=145)
        
        for item in links:
            link_id, url = item['id'], item['product_url']
            print("-" * 50)
            print(f"🔍 Đang quét Link ID {link_id}...")

            try:
                driver.get(url)
                
                # Kiểm tra Captcha
                if "punish" in driver.current_url or "verify" in driver.current_url:
                    print("  🛑 PHÁT HIỆN CAPTCHA! Hãy giải trên trình duyệt.")
                    input("  ⌨️ Nhấn ENTER sau khi giải xong -> ")
                    time.sleep(2)

                wait = WebDriverWait(driver, 15)
                res = {'price': 0, 'orig_price': 0, 'rating': 0, 'sold': 0, 'reviews': 0}

                # 1. Trích xuất Giá bán (Sale Price)
                try:
                    price_el = wait.until(EC.presence_of_element_located((By.CLASS_NAME, "pdp-v2-product-price-content-salePrice-amount")))
                    res['price'] = clean_price(price_el.text)
                except: pass

                # 2. Trích xuất Giá gốc (Original Price)
                try:
                    orig_el = driver.find_elements(By.CLASS_NAME, "pdp-price_type_deleted")
                    if orig_el: res['orig_price'] = clean_price(orig_el[0].text)
                except: pass

                # 3. Trích xuất Rating (Sao) & Review Count
                try:
                    star_el = driver.find_elements(By.CLASS_NAME, "score-average")
                    if star_el: res['rating'] = float(star_el[0].text.strip())
                    
                    rev_el = driver.find_elements(By.CLASS_NAME, "pdp-review-summary__link")
                    if rev_el: res['reviews'] = parse_number(rev_el[0].text)
                except: pass

                # 4. Trích xuất Lượt bán (Sold)
                try:
                    sold_el = driver.find_elements(By.XPATH, "//*[contains(text(), 'Đã bán')]")
                    if sold_el: res['sold'] = parse_number(sold_el[0].text)
                except: pass

                if res['price'] > 0:
                    print(f"  ✅ DONE: {res['price']}đ | ⭐ {res['rating']} sao | 📦 Bán: {res['sold']}")
                    update_db(link_id, res)
                else:
                    print("  ❌ Không lấy được giá. Có thể link lỗi.")

            except Exception as e:
                print(f"  ❌ Lỗi link: {e}")

            time.sleep(random.uniform(5, 8))

    except Exception as e:
        print(f"❌ Lỗi hệ thống: {e}")
    finally:
        if driver: driver.quit()
        print("\n🛑 Hoàn tất.")