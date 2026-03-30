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

sys.stdout.reconfigure(encoding='utf-8')

print("🚀 KHỞI ĐỘNG BOT LAZADA (BẢN ULTIMATE: TỐI ƯU DOM & BAXIA CAPTCHA)...")

HEADLESS_MODE = False  
BATCH_LIMIT = 5       

# ==========================================
# 1. CÁC HÀM XỬ LÝ SỐ LIỆU
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
    print("  Người máy đang cuộn nhẹ trang...")
    try:
        current_position = 0
        while current_position < 800:
            scroll_step = random.randint(150, 250)
            current_position += scroll_step
            driver.execute_script(f"window.scrollTo(0, {current_position});")
            time.sleep(random.uniform(0.1, 0.3))
    except Exception: pass

# ==========================================
# 2. CHƯƠNG TRÌNH CHÍNH & QUẢN LÝ DATABASE
# ==========================================
if __name__ == "__main__":
    try:
        db = mysql.connector.connect(host="127.0.0.1", port=3307, user="root", password="", database="web_test")
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

    # --- CẤU HÌNH CHROME ---
    options = uc.ChromeOptions()
    
    # [CHUẨN HÓA]: Dùng chung master_profile với các bot khác
    script_dir = os.path.dirname(os.path.abspath(__file__))
    profile_path = os.path.join(script_dir, "master_profile")
    options.add_argument(f"--user-data-dir={profile_path}")
    
    # Ép Chrome chạy ngầm 100% công suất
    options.add_argument("--disable-background-timer-throttling")
    options.add_argument("--disable-backgrounding-occluded-windows")
    options.add_argument("--disable-renderer-backgrounding")
    options.add_argument("--window-size=1920,1080") 
    
    if HEADLESS_MODE: options.add_argument("--headless=new")
    
    driver = None
    try:
        driver = uc.Chrome(options=options, version_main=145) 
        
        # BƯỚC MỒI LẤY COOKIE LAZADA TỪ TRANG CHỦ
        print("🌐 Đang truy cập trang chủ Lazada để lấy Trust Cookie...")
        try:
            driver.get("https://www.lazada.vn/")
            time.sleep(random.uniform(4, 6))
        except Exception as e:
            print("  ⚠️ Không thể tải trang chủ, tiếp tục chạy kịch bản...")

        for link in lazada_links:
            link_id, url = link['id'], link['product_url']
            print("-" * 50)
            print(f"🔍 Đang quét Link ID {link_id}...")
            
            try:
                driver.get(url)
                time.sleep(2.5)
                
                # 1. KIỂM TRA BAXIA CAPTCHA / LOGIN (CƠ CHẾ TỰ VỆ FAIL-FAST)
                current_url = driver.current_url.lower()
                if "login" in current_url or "captcha" in current_url or "baxia" in current_url:
                    if sys.stdin.isatty():
                        input("  🛑 Yêu cầu Login/Captcha thanh trượt! Giải quyết xong nhấn ENTER -> ")
                    else:
                        print("  ❌ PHÁT HIỆN BỊ CHẶN CAPTCHA LAZADA KHI CHẠY TRÊN WEB!")
                        print("  🛑 KÍCH HOẠT CHẾ ĐỘ TỰ VỆ: DỪNG TOÀN BỘ BOT NGAY LẬP TỨC!")
                        print("  👉 Vui lòng mở Terminal (CMD) chạy file lazada_crawler.py để giải Captcha trượt bằng tay!")
                        if driver: 
                            try: driver.quit()
                            except: pass
                        if 'db' in locals() and db.is_connected():
                            cursor.close()
                            db.close()
                        sys.exit(1)

                # 2. Cuộn NHẸ trang
                shallow_scroll(driver)

                # 3. Chờ phần tử giá (Dùng CSS Selector chọc thẳng vào Class giá thật)
                try:
                    WebDriverWait(driver, 10).until(
                        EC.presence_of_element_located((By.CSS_SELECTOR, "span[class*='salePrice-amount']"))
                    )
                except Exception:
                    print("  ⚠️ Trang tải chậm hoặc sản phẩm đã bị xóa/ẩn giá.")

                current_price, original_price = None, None
                historical_sold, rating_average, review_count = 0, 0.0, 0
                status = 2 

                # 4. TRÍCH XUẤT GIÁ LAZADA TẬN GỐC (DỰA TRÊN HTML DOM CHUẨN)
                try:
                    # Lấy giá trị hiện tại (Sale Price) - Tuyệt đối chỉ bắt thẻ có salePrice-amount
                    sale_elems = driver.find_elements(By.CSS_SELECTOR, "span[class*='salePrice-amount']")
                    for el in sale_elems:
                        text = el.text.strip()
                        possible_price = clean_price(text)
                        if possible_price and possible_price > 1000:
                            current_price = possible_price
                            status = 1
                            print(f"  💰 Giá hiện tại: {current_price} ₫ (Bắt chuẩn class salePrice-amount)")
                            break # Lấy phần tử hợp lệ đầu tiên
                    
                    # Lấy giá gốc (Original Price - Gạch chéo)
                    if status == 1:
                        orig_elems = driver.find_elements(By.CSS_SELECTOR, "span[class*='originalPrice-amount']")
                        for el in orig_elems:
                            text = el.text.strip()
                            p_orig = clean_price(text)
                            if p_orig and p_orig > current_price:
                                original_price = p_orig
                                print(f"  📉 Giá gốc (Gạch chéo): {original_price} ₫ (Bắt chuẩn class originalPrice-amount)")
                                break
                except Exception as e:
                    print(f"  ❌ Không bắt được giá theo cấu trúc: {e}")

                if status != 1:
                    print("  ❌ Không trích xuất được Giá hiện tại từ DOM.")

                # 5. TRÍCH XUẤT ĐÁNH GIÁ, SỐ SAO, ĐÃ BÁN (TỐI ƯU HÓA DOM LAZADA)
                if status == 1:
                    # LẤY "ĐÃ BÁN" (Dùng Regex do trộn lẫn trong văn bản)
                    body_text = str(driver.execute_script("return document.body.innerText;"))
                    sold_match = re.search(r'(?i)(đã bán\s*[\d\,\.]+[kKmMtrTR]?|[\d\,\.]+[kKmMtrTR]?\s*đã bán)', body_text)
                    if sold_match:
                        historical_sold = parse_historical_sold(sold_match.group(0))
                        print(f"  📦 Đã bán: {historical_sold}")

                    # LẤY "SỐ SAO" VÀ "SỐ LƯỢT ĐÁNH GIÁ" TRỰC TIẾP QUA CLASS
                    try:
                        # Số sao (VD: "5" hoặc "4.9")
                        star_elems = driver.find_elements(By.CSS_SELECTOR, ".container-star-v2-score")
                        if star_elems:
                            star_text = star_elems[0].text.strip()
                            rating_average = float(star_text)
                            print(f"  ⭐ Số sao: {rating_average} (Bắt qua Class)")
                            
                        # Số lượt đánh giá (VD: "(5979)")
                        review_elems = driver.find_elements(By.CSS_SELECTOR, ".container-star-v2-count")
                        if review_elems:
                            review_text = review_elems[0].text.strip()
                            # Xóa mọi ký tự không phải số (như dấu ngoặc đơn)
                            clean_review = re.sub(r'\D', '', review_text) 
                            if clean_review:
                                review_count = int(clean_review)
                                print(f"  💬 Số lượt đánh giá: {review_count} (Bắt qua Class)")
                    except Exception as e:
                        print(f"  ⚠️ Lỗi khi quét Số sao/Đánh giá: {e}")

                # 6. LƯU DATABASE TẠI CHỖ KÈM PING RECONNECT
                now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                
                try: db.ping(reconnect=True, attempts=3, delay=2)
                except Exception as e: print(f"  [!] Cảnh báo DB: {e}")

                if status == 1:
                    cursor.execute("""
                        UPDATE platform_links 
                        SET current_price=%s, original_price=%s, historical_sold=%s, rating_average=%s, review_count=%s, status=%s, last_scraped_at=%s
                        WHERE id=%s
                    """, (current_price, original_price, historical_sold, rating_average, review_count, status, now, link_id))
                    
                    cursor.execute("INSERT INTO price_history (link_id, price, scraped_at) VALUES (%s, %s, %s)", 
                                   (link_id, current_price, now))
                    db.commit()
                    print("  [+] Đã lưu thông tin vào Database.")
                else:
                    cursor.execute("UPDATE platform_links SET status=2, last_scraped_at=%s WHERE id=%s", (now, link_id))
                    db.commit()

            except Exception as e:
                print(f"  ❌ Lỗi bất ngờ: {e}")
                try: db.ping(reconnect=True, attempts=3, delay=2)
                except: pass
                cursor.execute("UPDATE platform_links SET status=3 WHERE id=%s", (link_id,))
                db.commit()

            # Nghỉ ngơi giữa các link
            time.sleep(random.uniform(3, 5)) 

        print("\n🎉 ĐÃ HOÀN THÀNH LÔ QUÉT LAZADA!")

    except Exception as e:
        print(f"❌ Lỗi hệ thống Bot nghiêm trọng: {e}")
    finally:
        if driver:
            try: driver.quit()
            except: pass
        if 'db' in locals() and db.is_connected():
            cursor.close()
            db.close()