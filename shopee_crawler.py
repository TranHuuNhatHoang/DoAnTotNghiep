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

print("🚀 KHỞI ĐỘNG BOT SHOPEE 2.9.2 (FIX LỖI NGỦ ĐÔNG TAB NGẦM)...")

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
            WHERE platform_name = 'Shopee' AND is_active = 1 
            ORDER BY last_scraped_at ASC LIMIT {BATCH_LIMIT}
        """
        cursor.execute(sql_batch)
        shopee_links = cursor.fetchall()
        cursor = db.cursor() 
    except Exception as e:
        print(f"❌ Lỗi kết nối Database: {e}"); sys.exit()

    print(f"📋 Tìm thấy {len(shopee_links)} sản phẩm Shopee.\n")
    if len(shopee_links) == 0: db.close(); sys.exit()

    # --- CẤU HÌNH CHROME (ĐÃ BỔ SUNG CỜ LỆNH CHỐNG NGỦ ĐÔNG) ---
    options = uc.ChromeOptions()
    profile_path = os.path.join(os.getcwd(), "master_profile")
    options.add_argument(f"--user-data-dir={profile_path}")
    
    # Ép Chrome chạy ngầm không bị giảm hiệu năng
    options.add_argument("--disable-background-timer-throttling")
    options.add_argument("--disable-backgrounding-occluded-windows")
    options.add_argument("--disable-renderer-backgrounding")
    options.add_argument("--window-size=1920,1080") # Đảm bảo Font-size luôn được tính toán đúng
    
    if HEADLESS_MODE: options.add_argument("--headless=new")
    
    driver = None
    try:
        driver = uc.Chrome(options=options, version_main=147) 
        
        # BƯỚC MỒI: LẤY TRUST COOKIE TỪ TRANG CHỦ
        print("🌐 Đang truy cập trang chủ Shopee để lấy Trust Cookie...")
        try:
            driver.get("https://shopee.vn/")
            time.sleep(random.uniform(4, 6))
        except Exception as e:
            print("  ⚠️ Không thể tải trang chủ, tiếp tục chạy kịch bản...")

        # BẮT ĐẦU VÒNG LẶP CÀO TỪNG SẢN PHẨM
        for link in shopee_links:
            link_id, url = link['id'], link['product_url']
            print("-" * 50)
            print(f"🔍 Đang quét Link ID {link_id}...")
            
            try:
                driver.get(url)
                time.sleep(1.5)
                
                # 1. Kiểm tra Login / Captcha
                # 1. Kiểm tra Login / Captcha
                current_url = driver.current_url.lower()
                if "login" in current_url or "captcha" in current_url or "verify" in current_url:
                    if sys.stdin.isatty():
                        input("  🛑 Yêu cầu Login/Captcha! Giải quyết xong nhấn ENTER -> ")
                    else:
                        print("  ❌ PHÁT HIỆN BỊ CHẶN CAPTCHA/LOGIN KHI CHẠY TRÊN WEB!")
                        print("  🛑 KÍCH HOẠT CHẾ ĐỘ TỰ VỆ: DỪNG TOÀN BỘ BOT NGAY LẬP TỨC!")
                        print("  👉 Hệ thống đã bảo toàn dữ liệu cũ. Vui lòng mở Terminal (CMD) chạy file này để giải Captcha bằng tay!")
                        
                        # Tuyệt đối không update Database để giữ nguyên trạng thái chờ cào cho link này.
                        
                        # Đóng tài nguyên an toàn và thoát khẩn cấp toàn bộ script
                        if driver: 
                            try: driver.quit()
                            except: pass
                        if 'db' in locals() and db.is_connected():
                            cursor.close()
                            db.close()
                        sys.exit(1) # Thoát Python với mã lỗi 1 để báo hiệu cho Web PHP biết

                # 2. Cuộn NHẸ trang
                shallow_scroll(driver)

                # 3. Chờ phần tử giá
                try:
                    WebDriverWait(driver, 10).until(
                        EC.presence_of_element_located((By.XPATH, "//*[contains(text(), '₫')]"))
                    )
                except Exception:
                    print("  ⚠️ Không thấy chữ '₫' nào. Có thể trang tải chậm.")

                # 4. TRÍCH XUẤT GIÁ BẰNG CỠ CHỮ
                current_price, original_price = None, None
                historical_sold, rating_average, review_count = 0, 0.0, 0
                status = 2 

                price_elements = driver.find_elements(By.XPATH, "//*[contains(text(), '₫')]")
                max_font_size = 0
                best_price_text = ""
                
                for el in price_elements:
                    try:
                        text = el.text.strip()
                        if len(text) <= 2: 
                            parent = el.find_element(By.XPATH, "..")
                            text = parent.text.strip()
                            font_size_str = parent.value_of_css_property('font-size')
                        else:
                            font_size_str = el.value_of_css_property('font-size')
                            
                        if len(text) > 40: continue
                            
                        font_size = float(font_size_str.replace('px', '').strip())
                        
                        if font_size > max_font_size:
                            max_font_size = font_size
                            best_price_text = text
                    except: continue

                if best_price_text:
                    parsed_price = clean_price(best_price_text)
                    if parsed_price and parsed_price > 1000:
                        current_price = parsed_price
                        status = 1
                        print(f"  💰 Giá hiện tại: {current_price} ₫ (Bắt bằng Cỡ chữ: {max_font_size}px)")
                else:
                    print("  ❌ Không trích xuất được Giá hiện tại từ DOM.")

                # 5. TRÍCH XUẤT ĐÁNH GIÁ, SỐ SAO, ĐÃ BÁN (FIX LỖI TREO BẰNG JAVASCRIPT)
                if status == 1:
                    # Tuyệt chiêu: Dùng JS hút toàn bộ text trong 0.01 giây, không làm treo Selenium
                    body_text = str(driver.execute_script("return document.body.innerText;"))
                    
                    sold_match = re.search(r'(?i)([\d,\.]+[kKmMtrTR]?)\s*đã bán', body_text)
                    if sold_match:
                        historical_sold = parse_historical_sold(sold_match.group(0))
                        print(f"  📦 Đã bán: {historical_sold}")

                    rev_match = re.search(r'(?i)([\d,\.]+[kKmMtrTR]?)\s*đánh giá', body_text)
                    if rev_match:
                        review_count = parse_historical_sold(rev_match.group(0))
                        print(f"  💬 Số lượt đánh giá: {review_count}")
                        
                    star_match = re.search(r'(?i)([0-5]\.[0-9])[^\d]*?([\d,\.]+[kKmMtrTR]?)\s*đánh giá', body_text)
                    if star_match:
                        rating_average = float(star_match.group(1))
                        print(f"  ⭐ Số sao: {rating_average}")
                        
                    # Quét lại giá gốc an toàn
                    orig_elements = driver.find_elements(By.XPATH, "//*[contains(text(), '₫')]")
                    for el in orig_elements:
                        try:
                            text = el.text.strip()
                            if len(text) <= 2: text = el.find_element(By.XPATH, "..").text.strip()
                            if len(text) > 40: continue
                            
                            p_orig = clean_price(text)
                            if p_orig and p_orig > current_price:
                                original_price = p_orig
                                print(f"  📉 Giá gốc (Gạch chéo): {original_price} ₫")
                                break
                        except: pass

                # 6. LƯU DATABASE TẠI CHỖ (KÈM KÍCH TIM CHỐNG TIMEOUT SAU KHI GIẢI CAPTCHA LÂU)
                now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                
                # Kích tim Database để đảm bảo kết nối không bị đứt
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

            time.sleep(random.uniform(3, 5)) 

        print("\n🎉 ĐÃ HOÀN THÀNH LÔ QUÉT SHOPEE 2.9.2!")

    except Exception as e:
        print(f"❌ Lỗi hệ thống Bot nghiêm trọng: {e}")
    finally:
        if driver:
            try: driver.quit()
            except: pass
        if 'db' in locals() and db.is_connected():
            cursor.close()
            db.close()