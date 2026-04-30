import undetected_chromedriver as uc
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
import mysql.connector
import time
import os
import random
import re
from datetime import datetime
import sys
from app_config import get_chrome_version_main, get_db_config, get_profile_path

sys.stdout.reconfigure(encoding='utf-8')

print("🚀 KHỞI ĐỘNG BOT LAZADA (BẢN ỔN ĐỊNH: LẤY FULL DATA + CHỐNG TRÀN RAM)...")

HEADLESS_MODE = False  
BATCH_LIMIT = 100     # TỔNG SỐ LINK muốn quét 
CHUNK_SIZE = 25       # Số link cào liên tục trước khi reset RAM

# ==========================================
# 1. CÁC HÀM XỬ LÝ SỐ LIỆU (Giữ nguyên logic cũ cực tốt)
# ==========================================
def clean_price(raw_price_str):
    try:
        min_price_str = str(raw_price_str).split('-')[0]
        digits = re.sub(r'\D', '', min_price_str) 
        return int(digits) if digits else None
    except:
        return None

def parse_historical_sold(sold_text):
    try:
        m = re.search(r'([\d\,\.]+)\s*([kKmMtrTR]?)', sold_text)
        if not m: return 0
        num_str = m.group(1)
        multiplier = m.group(2).lower() if m.group(2) else ''
        if multiplier:
            num_str = num_str.replace(',', '.') 
            val = float(num_str)
            if multiplier == 'k': val *= 1000
            elif multiplier in ['m', 'tr']: val *= 1000000
            return int(val)
        else:
            clean_num = re.sub(r'\D', '', num_str)
            return int(clean_num) if clean_num else 0
    except Exception: return 0

def shallow_scroll(driver):
    print("  Người máy đang cuộn nhẹ trang để Load Data...")
    try:
        current_position = 0
        while current_position < 900:
            scroll_step = random.randint(150, 250)
            current_position += scroll_step
            driver.execute_script(f"window.scrollTo(0, {current_position});")
            time.sleep(random.uniform(0.1, 0.3))
    except Exception: pass

# ==========================================
# 2. HÀM TẠO DRIVER 
# ==========================================
def create_stable_driver():
    options = uc.ChromeOptions()
    profile_path = get_profile_path("master_profile")
    options.add_argument(f"--user-data-dir={profile_path}")
    
    options.add_argument("--disable-background-timer-throttling")
    options.add_argument("--disable-backgrounding-occluded-windows")
    options.add_argument("--disable-renderer-backgrounding")
    options.add_argument("--window-size=1920,1080") 
    
    # CHỈ chặn hình ảnh để nhẹ RAM, để cho JavaScript hoạt động bình thường
    prefs = {
        "profile.managed_default_content_settings.images": 2, 
        "profile.default_content_setting_values.notifications": 2
    }
    options.add_experimental_option("prefs", prefs)

    if HEADLESS_MODE: options.add_argument("--headless=new")
    
    driver = uc.Chrome(options=options, version_main=get_chrome_version_main(147)) 
    driver.set_page_load_timeout(45) # Đủ thời gian cho Lazada load script
    return driver

# ==========================================
# 3. CHƯƠNG TRÌNH CHÍNH & QUẢN LÝ DATABASE
# ==========================================
if __name__ == "__main__":
    try:
        db = mysql.connector.connect(**get_db_config())
        cursor = db.cursor(dictionary=True)
        
        sql_batch = f"""
            SELECT id, product_url 
            FROM platform_links 
            WHERE platform_name = 'Lazada' AND is_active = 1 
            ORDER BY last_scraped_at ASC LIMIT {BATCH_LIMIT}
        """
        cursor.execute(sql_batch)
        lazada_links = cursor.fetchall()
        cursor = db.cursor() 
    except Exception as e:
        print(f"❌ Lỗi kết nối Database: {e}"); sys.exit()

    print(f"📋 Tìm thấy {len(lazada_links)} sản phẩm Lazada.\n")
    if len(lazada_links) == 0: db.close(); sys.exit()

    chunks = [lazada_links[i:i + CHUNK_SIZE] for i in range(0, len(lazada_links), CHUNK_SIZE)]

    for chunk_index, current_chunk in enumerate(chunks):
        print(f"\n==================================================")
        print(f"🔥 BẮT ĐẦU MẺ QUÉT {chunk_index + 1}/{len(chunks)} (Số lượng: {len(current_chunk)} link)")
        print(f"==================================================")
        
        driver = None
        try:
            driver = create_stable_driver()
            
            print("🌐 Đang lấy Trust Cookie từ trang chủ...")
            try:
                driver.get("https://www.lazada.vn/")
                time.sleep(random.uniform(3, 5))
            except Exception: pass

            for link in current_chunk:
                link_id, url = link['id'], link['product_url']
                print("-" * 50)
                print(f"🔍 Đang quét Link ID {link_id}...")
                
                try:
                    driver.get(url)
                    # Chờ trang render giao diện cơ bản như file cũ
                    time.sleep(2.5) 
                    
                    # 1. KIỂM TRA BAXIA CAPTCHA
                    current_url = driver.current_url.lower()
                    if "login" in current_url or "captcha" in current_url or "baxia" in current_url:
                        if sys.stdin.isatty():
                            input("  🛑 Dính Captcha! Kéo tay xong nhấn ENTER -> ")
                        else:
                            print("  ❌ BỊ CHẶN CAPTCHA! BỎ QUA MẺ NÀY!")
                            break

                    # 2. CUỘN TRANG (Kích hoạt Lazy Load của Lazada)
                    shallow_scroll(driver)
                    # Phải chờ thêm chút nữa để JS gọi API đổ dữ liệu Review/Sold ra màn hình
                    time.sleep(1.5) 

                    # 3. CHỜ PHẦN TỬ GIÁ
                    try:
                        WebDriverWait(driver, 8).until(
                            EC.presence_of_element_located((By.CSS_SELECTOR, "span[class*='salePrice-amount']"))
                        )
                    except Exception: pass

                    current_price, original_price = None, None
                    historical_sold, rating_average, review_count = 0, 0.0, 0
                    status = 2 

                    # 4. TRÍCH XUẤT FULL DỮ LIỆU
                    try:
                        sale_elems = driver.find_elements(By.CSS_SELECTOR, "span[class*='salePrice-amount']")
                        for el in sale_elems:
                            text = el.text.strip()
                            possible_price = clean_price(text)
                            if possible_price and possible_price > 1000:
                                current_price = possible_price
                                status = 1
                                print(f"  💰 Giá hiện tại: {current_price} ₫")
                                break
                        
                        if status == 1:
                            orig_elems = driver.find_elements(By.CSS_SELECTOR, "span[class*='originalPrice-amount']")
                            for el in orig_elems:
                                p_orig = clean_price(el.text.strip())
                                if p_orig and p_orig > current_price:
                                    original_price = p_orig
                                    print(f"  📉 Giá gốc: {original_price} ₫")
                                    break
                    except Exception: pass

                    if status == 1:
                        # Dùng innerText toàn trang để vét "Đã bán"
                        body_text = str(driver.execute_script("return document.body.innerText;"))
                        sold_match = re.search(r'(?i)(đã bán\s*[\d\,\.]+[kKmMtrTR]?|[\d\,\.]+[kKmMtrTR]?\s*đã bán)', body_text)
                        if sold_match:
                            historical_sold = parse_historical_sold(sold_match.group(0))
                            print(f"  📦 Đã bán: {historical_sold}")

                        # Vét Sao & Đánh giá (do đã cuộn trang + sleep nên DOM đã có cái này)
                        try:
                            star_elems = driver.find_elements(By.CSS_SELECTOR, ".container-star-v2-score")
                            if star_elems: 
                                rating_average = float(star_elems[0].text.strip())
                                print(f"  ⭐ Sao: {rating_average}")
                                
                            review_elems = driver.find_elements(By.CSS_SELECTOR, ".container-star-v2-count")
                            if review_elems:
                                clean_review = re.sub(r'\D', '', review_elems[0].text.strip()) 
                                if clean_review: 
                                    review_count = int(clean_review)
                                    print(f"  💬 Đánh giá: {review_count}")
                        except Exception as e: print(f"  ⚠️ Khuyết thông tin Review: {e}")

                    # 5. LƯU DATABASE TẠI CHỖ
                    now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                    try: db.ping(reconnect=True, attempts=3, delay=2)
                    except Exception: pass

                    if status == 1:
                        cursor.execute("""
                            UPDATE platform_links 
                            SET current_price=%s, original_price=%s, historical_sold=%s, rating_average=%s, review_count=%s, status=%s, last_scraped_at=%s
                            WHERE id=%s
                        """, (current_price, original_price, historical_sold, rating_average, review_count, status, now, link_id))
                        
                        cursor.execute("INSERT INTO price_history (link_id, price, scraped_at) VALUES (%s, %s, %s)", 
                                       (link_id, current_price, now))
                        db.commit()
                        print("  [+] Đã lưu Full thông tin vào DB.")
                    else:
                        cursor.execute("UPDATE platform_links SET status=2, last_scraped_at=%s WHERE id=%s", (now, link_id))
                        db.commit()
                        print("  ❌ Lỗi: Không trích xuất được giá gốc.")

                except Exception as e:
                    print(f"  ⚠️ Lỗi TimeOut/Mạng chậm: {e}")
                    try: db.ping(reconnect=True, attempts=3, delay=2)
                    except: pass
                    cursor.execute("UPDATE platform_links SET status=3 WHERE id=%s", (link_id,))
                    db.commit()

                # Nghỉ ngắn giữa các link
                time.sleep(random.uniform(3, 5)) 
                
        except Exception as e:
            print(f"❌ Lỗi văng trình duyệt mẻ này: {e}")
        finally:
            if driver:
                try: 
                    driver.quit() 
                    print("\n♻️ Đã tắt trình duyệt, xả sạch RAM cho mẻ này.")
                except: pass
        
        # Nghỉ ngơi cho IP hạ nhiệt
        if chunk_index < len(chunks) - 1:
            wait_time = 15
            print(f"⏳ Nghỉ {wait_time} giây trước mẻ tiếp theo...")
            time.sleep(wait_time)

    if 'db' in locals() and db.is_connected():
        cursor.close()
        db.close()
        
    print("\n🎉 HOÀN TẤT CHIẾN DỊCH QUÉT LAZADA!")
