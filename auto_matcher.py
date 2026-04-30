import undetected_chromedriver as uc
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
import mysql.connector
import time
import os
from thefuzz import fuzz # Thư viện so khớp chuỗi
import sys
from app_config import get_chrome_version_main, get_db_config, get_profile_path
sys.stdout.reconfigure(encoding='utf-8')

print("🤖 KHỞI ĐỘNG BOT TỰ ĐỘNG GOM NHÓM (FUZZY MATCHER)...")

# ==========================================
# 1. HÀM TÌM SẢN PHẨM CHƯA CÓ LINK SHOPEE
# ==========================================
def get_products_without_shopee():
    """Lấy các sản phẩm gốc (Tiki) mà chưa được map với link Shopee nào"""
    try:
        db = mysql.connector.connect(**get_db_config())
        cursor = db.cursor(dictionary=True)
        # Câu lệnh SQL logic cực hay: Lấy product mà không tồn tại trong platform_links với platform = 'Shopee'
        sql = """
            SELECT p.id, p.name 
            FROM products p 
            LEFT JOIN platform_links pl ON p.id = pl.product_id AND pl.platform_name = 'Shopee'
            WHERE pl.id IS NULL
        """
        cursor.execute(sql)
        results = cursor.fetchall()
        db.close()
        return results
    except Exception as e:
        print(f"❌ Lỗi truy vấn Database: {e}")
        return []

# ==========================================
# 2. HÀM LƯU KẾT QUẢ VÀO DATABASE
# ==========================================
def save_matched_link(product_id, shopee_url, match_score):
    try:
        db = mysql.connector.connect(**get_db_config())
        cursor = db.cursor()
        
        # Thêm mới (INSERT) link Shopee này, gán chung product_id với sản phẩm gốc
        sql = """
            INSERT INTO platform_links (product_id, platform_name, product_url, current_price, status, is_active, match_score) 
            VALUES (%s, 'Shopee', %s, 0, 0, 1, %s)
        """
        # status = 0 (Chờ cào giá), current_price = 0 (Sẽ được Bot Shopee 2.0 quét sau)
        cursor.execute(sql, (product_id, shopee_url, match_score))
        db.commit()
        db.close()
        print(f"  ✅ Đã GHI NHẬN thành công vào Database! (Score: {match_score}/100)")
    except Exception as e:
        print(f"  ❌ Lỗi lưu DB: {e}")

# ==========================================
# 3. CHƯƠNG TRÌNH CHÍNH (ĐI SĂN)
# ==========================================
if __name__ == "__main__":
    target_products = get_products_without_shopee()
    
    if not target_products:
        print("🎉 Tuyệt vời! Tất cả sản phẩm gốc đều đã có link Shopee theo dõi.")
        exit()
        
    print(f"📋 Tìm thấy {len(target_products)} sản phẩm gốc CẦN TÌM link Shopee.")

    # Mở trình duyệt tàng hình với Profile
    options = uc.ChromeOptions()
    profile_path = get_profile_path("shopee_profile")
    options.add_argument(f"--user-data-dir={profile_path}")
    
    driver = None
    try:
        driver = uc.Chrome(options=options, version_main=get_chrome_version_main(145))
        
        for product in target_products:
            p_id = product['id']
            p_name = product['name']
            
            print("-" * 50)
            print(f"🔍 Đang tìm kiếm cho: [{p_name}]")
            
            # Khéo léo truyền từ khóa vào URL tìm kiếm của Shopee
            search_url = f"https://shopee.vn/search?keyword={p_name.replace(' ', '%20')}"
            driver.get(search_url)
            time.sleep(7) # Đợi danh sách sản phẩm load xong
            
            # Cuộn chuột xuống một chút để Shopee load hình và chữ
            driver.execute_script("window.scrollBy(0, 500);")
            time.sleep(2)

            try:
                # [ĐÃ NÂNG CẤP]: Tìm thẻ <a> có chứa URL kiểu sản phẩm Shopee (-i.)
                product_cards = driver.find_elements(By.XPATH, "//a[contains(@href, '-i.')]")
                
                best_match_url = None
                best_match_score = 0
                best_match_name = ""

                # Quét 7 kết quả đầu tiên (trừ hao các thẻ quảng cáo hiển thị lỗi)
                for card in product_cards[:7]:
                    # Tách tất cả text trong thẻ ra thành nhiều dòng
                    text_lines = card.text.strip().split('\n')
                    
                    # Bỏ qua nếu thẻ rỗng
                    if len(text_lines) == 0 or text_lines[0] == '':
                        continue
                        
                    # MẸO: Tên sản phẩm thường là dòng dài nhất, các dòng ngắn là "Mall", "Yêu thích"...
                    shopee_name = max(text_lines, key=len) 
                    shopee_url = card.get_attribute("href")
                    
                    if shopee_name and shopee_url:
                        # THUẬT TOÁN CHẤM ĐIỂM
                        score = fuzz.token_set_ratio(p_name.lower(), shopee_name.lower())
                        print(f"  Tiềm năng: {shopee_name[:40]}... -> Điểm: {score}")
                        
                        if score > best_match_score:
                            best_match_score = score
                            best_match_url = shopee_url
                            best_match_name = shopee_name

                # Bắt đầu ra quyết định
                if best_match_score >= 80: # Ngưỡng tự tin
                    print(f"  🎯 TÌM THẤY CHÂN ÁI: {best_match_name[:50]}...")
                    clean_url = best_match_url.split('?')[0] if '?' in best_match_url else best_match_url
                    save_matched_link(p_id, clean_url, best_match_score)
                else:
                    print(f"  ⚠️ Điểm cao nhất chỉ đạt {best_match_score}. KHÔNG TỰ TIN GÁN LINK.")

            except Exception as e:
                print(f"  ❌ Lỗi khi đọc HTML tìm kiếm: {e}")

            time.sleep(3) # Nghỉ ngơi trước khi tìm sản phẩm tiếp theo

    except Exception as e:
        print(f"❌ Lỗi hệ thống Bot Đi Săn: {e}")
    finally:
        print("🛑 Đóng trình duyệt ảo.")
        if driver:
            try: driver.quit()
            except: pass
