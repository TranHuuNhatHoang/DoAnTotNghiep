import requests
import re
import mysql.connector
from datetime import datetime
import sys
sys.stdout.reconfigure(encoding='utf-8')

# ==========================================
# PHẦN 1: CÁC HÀM CÔNG CỤ & CÀO DỮ LIỆU
# ==========================================
def extract_tiki_ids(url):
    """Trích xuất Product ID và SPID từ link Tiki"""
    product_id_match = re.search(r'-p(\d+)\.html', url)
    product_id = product_id_match.group(1) if product_id_match else None
    
    spid_match = re.search(r'spid=(\d+)', url)
    spid = spid_match.group(1) if spid_match else None
    
    return product_id, spid

def scrape_tiki_data(product_url):
    """Cào dữ liệu từ API của Tiki dựa trên link sản phẩm"""
    print(f"[*] Đang xử lý link: {product_url}")
    p_id, s_id = extract_tiki_ids(product_url)
    
    # SỬA LỖI Ở ĐÂY: Chỉ bắt lỗi nếu thiếu Product ID (p_id)
    if not p_id:
        print("[!] Lỗi: Không thể trích xuất Product ID từ link này.")
        return None 

    # SỬA LỖI Ở ĐÂY: Tạo link API động tùy theo việc có spid hay không
    if s_id:
        api_url = f"https://tiki.vn/api/v2/products/{p_id}?platform=web&spid={s_id}&version=3"
        print(f"[-] Đã tìm thấy SPID: {s_id}")
    else:
        api_url = f"https://tiki.vn/api/v2/products/{p_id}?platform=web&version=3"
        print("[-] Link không có SPID, sẽ dùng giá mặc định của Tiki.")
    
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    try:
        response = requests.get(api_url, headers=headers, timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            product_info = {
                "name": data.get('name'),
                "current_price": data.get('price'),
                "original_price": data.get('original_price'),
                "historical_sold": data.get('quantity_sold', {}).get('value', 0),
                "rating_average": data.get('rating_average', 0),
                "review_count": data.get('review_count', 0),
                "status": 1 
            }
            print("[+] Cào thành công!")
            return product_info
        else:
            print(f"[!] Lỗi HTTP: {response.status_code}")
            return {"status": 2} 
            
    except requests.exceptions.RequestException as e:
        print(f"[!] Lỗi kết nối: {e}")
        return {"status": 3}
    """Cào dữ liệu từ API của Tiki dựa trên link sản phẩm"""
    print(f"[*] Đang xử lý link: {product_url}")
    p_id, s_id = extract_tiki_ids(product_url)
    
    if not p_id or not s_id:
        print("[!] Lỗi: Không thể trích xuất Product ID hoặc SPID từ link này.")
        return None 

    api_url = f"https://tiki.vn/api/v2/products/{p_id}?platform=web&spid={s_id}&version=3"
    
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }
    
    try:
        response = requests.get(api_url, headers=headers, timeout=10)
        
        if response.status_code == 200:
            data = response.json()
            product_info = {
                "name": data.get('name'),
                "current_price": data.get('price'),
                "original_price": data.get('original_price'),
                "historical_sold": data.get('quantity_sold', {}).get('value', 0),
                "rating_average": data.get('rating_average', 0),
                "review_count": data.get('review_count', 0),
                "status": 1 
            }
            print("[+] Cào thành công!")
            return product_info
        else:
            print(f"[!] Lỗi HTTP: {response.status_code}")
            return {"status": 2} 
            
    except requests.exceptions.RequestException as e:
        print(f"[!] Lỗi kết nối: {e}")
        return {"status": 3} 

# ==========================================
# PHẦN 2: HÀM KẾT NỐI VÀ XỬ LÝ DATABASE
# ==========================================
def update_tiki_prices_to_db():
    """Lấy link từ DB, đi cào dữ liệu và lưu lại kết quả vào DB"""
    try:
        print("[*] Đang kết nối Database...")
        conn = mysql.connector.connect(
            host="127.0.0.1",
            port=3307,        
            user="root",      
            password="",      
            database="web_test"
        )
        cursor = conn.cursor(dictionary=True) 
        print("[+] Kết nối Database thành công!\n")

        # Lấy các link Tiki đang active
        cursor.execute("SELECT id, product_url FROM platform_links WHERE platform_name = 'Tiki' AND is_active = 1")
        tiki_links = cursor.fetchall()
        
        print(f"[*] Tìm thấy {len(tiki_links)} link Tiki cần cập nhật.")

        # Vòng lặp xử lý từng link
        for link in tiki_links:
            link_id = link['id']
            url = link['product_url']
            
            data = scrape_tiki_data(url) 
            
            if data and data.get('status') == 1:
                # Cập nhật thông tin tổng quan
                update_sql = """
                    UPDATE platform_links 
                    SET current_price = %s, original_price = %s, historical_sold = %s, 
                        rating_average = %s, review_count = %s, status = %s, last_scraped_at = %s
                    WHERE id = %s
                """
                now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                update_values = (
                    data['current_price'], data['original_price'], data['historical_sold'],
                    data['rating_average'], data['review_count'], data['status'], now, link_id
                )
                cursor.execute(update_sql, update_values)

                # Lưu lịch sử giá
                insert_sql = "INSERT INTO price_history (link_id, price, scraped_at) VALUES (%s, %s, %s)"
                insert_values = (link_id, data['current_price'], now)
                cursor.execute(insert_sql, insert_values)
                
                print(f"[+] Đã cập nhật xong vào Database cho link ID: {link_id}\n")
            else:
                print(f"[!] Bỏ qua link ID: {link_id} do lỗi cào dữ liệu.\n")

        conn.commit()
        print("[+] ĐÃ LƯU TOÀN BỘ THAY ĐỔI VÀO DATABASE!")

    except mysql.connector.Error as err:
        print(f"[!] Lỗi MySQL: {err}")
    finally:
        if 'conn' in locals() and conn.is_connected():
            cursor.close()
            conn.close()
            print("[*] Đã đóng kết nối Database.")

# ==========================================
# PHẦN 3: LỆNH KÍCH HOẠT CHƯƠNG TRÌNH
# ==========================================
if __name__ == "__main__":
    print("=== BẮT ĐẦU CHƯƠNG TRÌNH CẬP NHẬT GIÁ TIKI ===")
    update_tiki_prices_to_db()
    print("=== KẾT THÚC CHƯƠNG TRÌNH ===")