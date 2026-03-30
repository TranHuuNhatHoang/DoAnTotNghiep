import undetected_chromedriver as uc
from selenium.webdriver.common.by import By
import mysql.connector
import time
import os
import random
from thefuzz import fuzz # Thư viện so khớp chuỗi
import sys

sys.stdout.reconfigure(encoding='utf-8')

print("🤖 KHỞI ĐỘNG BOT TỰ ĐỘNG GOM NHÓM (MULTI-PLATFORM FUZZY MATCHER)...")

# ==========================================
# 1. HÀM TÌM SẢN PHẨM CHƯA CÓ LINK THEO NỀN TẢNG
# ==========================================
def get_products_missing_platform(platform_name):
    """Lấy các sản phẩm gốc (Tiki) mà chưa được map với link của nền tảng chỉ định"""
    try:
        db = mysql.connector.connect(host="127.0.0.1", port=3307, user="root", password="", database="web_test")
        cursor = db.cursor(dictionary=True)
        sql = """
            SELECT p.id, p.name 
            FROM products p 
            LEFT JOIN platform_links pl ON p.id = pl.product_id AND pl.platform_name = %s
            WHERE pl.id IS NULL
        """
        cursor.execute(sql, (platform_name,))
        results = cursor.fetchall()
        db.close()
        return results
    except Exception as e:
        print(f"❌ Lỗi truy vấn Database: {e}")
        return []

# ==========================================
# 2. HÀM LƯU KẾT QUẢ VÀO DATABASE
# ==========================================
def save_matched_link(product_id, platform_name, matched_url, match_score):
    try:
        db = mysql.connector.connect(host="127.0.0.1", port=3307, user="root", password="", database="web_test")
        cursor = db.cursor()
        
        sql = """
            INSERT INTO platform_links (product_id, platform_name, product_url, current_price, status, match_score) 
            VALUES (%s, %s, %s, 0, 0, %s)
        """
        cursor.execute(sql, (product_id, platform_name, matched_url, match_score))
        db.commit()
        db.close()
        print(f"  ✅ Đã GHI NHẬN {platform_name} thành công vào Database! (Score: {match_score}/100)")
    except Exception as e:
        print(f"  ❌ Lỗi lưu DB: {e}")

# ==========================================
# 3. CHƯƠNG TRÌNH CHÍNH (RADAR ĐI SĂN)
# ==========================================
if __name__ == "__main__":
    # Khởi tạo trình duyệt dùng chung cho cả 2 nền tảng (Kèm cờ chống ngủ đông)
    options = uc.ChromeOptions()
    
    # [ĐÃ FIX]: Lấy đường dẫn tuyệt đối của thư mục chứa file code này
    profile_path = os.path.join(os.getcwd(), "master_profile")
    options.add_argument(f"--user-data-dir={profile_path}")
    
    options.add_argument(f"--user-data-dir={profile_path}")
    options.add_argument("--disable-background-timer-throttling")
    options.add_argument("--disable-backgrounding-occluded-windows")
    options.add_argument("--disable-renderer-backgrounding")
    options.add_argument("--window-size=1920,1080")
    
    driver = None
    try:
        driver = uc.Chrome(options=options, version_main=145)
        
        platforms_to_hunt = ['Shopee', 'Lazada']
        
        for platform in platforms_to_hunt:
            print("\n" + "="*60)
            print(f"🚀 BẮT ĐẦU CHIẾN DỊCH QUÉT TÌM LINK CHO SÀN: {platform.upper()}")
            print("="*60)
            
            target_products = get_products_missing_platform(platform)
            
            if not target_products:
                print(f"🎉 Tuyệt vời! Tất cả sản phẩm gốc đều đã có link {platform} theo dõi.")
                continue
                
            print(f"📋 Tìm thấy {len(target_products)} sản phẩm gốc CẦN TÌM link {platform}.")

            for product in target_products:
                p_id = product['id']
                p_name = product['name']
                
                print("-" * 50)
                print(f"🔍 Đang tìm kiếm [{platform}] cho: [{p_name}]")
                
                encoded_name = p_name.replace(' ', '%20')
                if platform == 'Shopee':
                    search_url = f"https://shopee.vn/search?keyword={encoded_name}"
                    xpath_selector = "//a[contains(@href, '-i.')]"
                elif platform == 'Lazada':
                    search_url = f"https://www.lazada.vn/catalog/?q={encoded_name}"
                    xpath_selector = "//a[contains(@href, '.html')]"
                
                driver.get(search_url)
                time.sleep(2.5) # Chờ URL định tuyến xong
                
                # --- KIỂM TRA LOGIN / CAPTCHA (CƠ CHẾ TỰ VỆ) ---
                current_url = driver.current_url.lower()
                if "login" in current_url or "captcha" in current_url or "baxia" in current_url or "verify" in current_url:
                    if sys.stdin.isatty():
                        input(f"  🛑 Yêu cầu Login/Captcha từ {platform}! Giải quyết xong nhấn ENTER -> ")
                    else:
                        print(f"  ❌ PHÁT HIỆN BỊ CHẶN CAPTCHA TRÊN {platform.upper()} KHI CHẠY TRÊN WEB!")
                        print("  🛑 KÍCH HOẠT CHẾ ĐỘ TỰ VỆ: DỪNG BOT NGAY LẬP TỨC!")
                        print("  👉 Vui lòng mở Terminal (CMD) chạy file multi_platform_matcher.py để giải quyết!")
                        
                        if driver: 
                            try: driver.quit()
                            except: pass
                        sys.exit(1) # Thoát an toàn báo lỗi về Web

                # Cuộn trang để hiển thị sản phẩm
                time.sleep(random.uniform(2, 4)) 
                driver.execute_script("window.scrollBy(0, 600);")
                time.sleep(2)

                try:
                    product_cards = driver.find_elements(By.XPATH, xpath_selector)
                    
                    best_match_url = None
                    best_match_score = 0
                    best_match_name = ""

                    for card in product_cards[:8]:
                        text_lines = card.text.strip().split('\n')
                        
                        if len(text_lines) == 0 or text_lines[0] == '':
                            continue
                            
                        extracted_name = max(text_lines, key=len) 
                        extracted_url = card.get_attribute("href")
                        
                        if extracted_name and extracted_url:
                            score = fuzz.token_set_ratio(p_name.lower(), extracted_name.lower())
                            print(f"  Tiềm năng: {extracted_name[:40]}... -> Điểm: {score}")
                            
                            if score > best_match_score:
                                best_match_score = score
                                best_match_url = extracted_url
                                best_match_name = extracted_name

                    if best_match_score >= 80:
                        print(f"  🎯 TÌM THẤY CHÂN ÁI: {best_match_name[:50]}...")
                        clean_url = best_match_url.split('?')[0] if '?' in best_match_url else best_match_url
                        save_matched_link(p_id, platform, clean_url, best_match_score)
                    else:
                        print(f"  ⚠️ Điểm cao nhất chỉ đạt {best_match_score}. KHÔNG TỰ TIN GÁN LINK.")

                except Exception as e:
                    print(f"  ❌ Lỗi khi đọc HTML tìm kiếm của {platform}: {e}")

                time.sleep(random.uniform(3, 5))

    except Exception as e:
        print(f"❌ Lỗi hệ thống Bot Đi Săn: {e}")
    finally:
        print("\n🛑 Hoàn thành chiến dịch. Đóng trình duyệt ảo.")
        if driver:
            try: driver.quit()
            except: pass