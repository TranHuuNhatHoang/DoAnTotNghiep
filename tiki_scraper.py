import re
import sys
from datetime import datetime

import mysql.connector
import requests

from app_config import get_db_config

sys.stdout.reconfigure(encoding="utf-8")


def extract_tiki_ids(url):
    product_id_match = re.search(r"-p(\d+)\.html", url)
    spid_match = re.search(r"spid=(\d+)", url)
    product_id = product_id_match.group(1) if product_id_match else None
    spid = spid_match.group(1) if spid_match else None
    return product_id, spid


def normalize_image_url(url):
    if not url:
        return None

    url = str(url).strip()
    if not url:
        return None
    if url.startswith("//"):
        return "https:" + url
    if url.startswith("/"):
        return "https://salt.tikicdn.com" + url
    return url


def extract_thumbnail_url(product_data):
    direct_url = normalize_image_url(product_data.get("thumbnail_url"))
    if direct_url:
        return direct_url

    for image in product_data.get("images", []) or []:
        for key in ("base_url", "large_url", "medium_url", "small_url", "thumbnail_url"):
            image_url = normalize_image_url(image.get(key))
            if image_url:
                return image_url

    return None


def scrape_tiki_data(product_url):
    print(f"[*] Dang xu ly link: {product_url}")
    product_id, spid = extract_tiki_ids(product_url)

    if not product_id:
        print("[!] Khong the trich xuat Product ID tu link Tiki.")
        return None

    api_url = f"https://tiki.vn/api/v2/products/{product_id}?platform=web&version=3"
    if spid:
        api_url += f"&spid={spid}"

    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) "
        "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }

    try:
        response = requests.get(api_url, headers=headers, timeout=10)
        if response.status_code != 200:
            print(f"[!] Loi HTTP: {response.status_code}")
            return {"status": 2}

        data = response.json()
        print("[+] Cao thanh cong!")
        return {
            "name": data.get("name"),
            "thumbnail_url": extract_thumbnail_url(data),
            "current_price": data.get("price"),
            "original_price": data.get("original_price"),
            "historical_sold": data.get("quantity_sold", {}).get("value", 0),
            "rating_average": data.get("rating_average", 0),
            "review_count": data.get("review_count", 0),
            "status": 1,
        }
    except requests.exceptions.RequestException as exc:
        print(f"[!] Loi ket noi: {exc}")
        return {"status": 3}


def update_tiki_prices_to_db():
    conn = None
    cursor = None

    try:
        print("[*] Dang ket noi Database...")
        conn = mysql.connector.connect(**get_db_config())
        cursor = conn.cursor(dictionary=True)
        print("[+] Ket noi Database thanh cong!\n")

        cursor.execute(
            "SELECT id, product_id, product_url FROM platform_links "
            "WHERE platform_name = 'Tiki' AND is_active = 1"
        )
        tiki_links = cursor.fetchall()
        print(f"[*] Tim thay {len(tiki_links)} link Tiki can cap nhat.")

        for link in tiki_links:
            link_id = link["id"]
            product_id = link["product_id"]
            data = scrape_tiki_data(link["product_url"])
            now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")

            if data and data.get("status") == 1:
                cursor.execute(
                    """
                    UPDATE platform_links
                    SET current_price = %s, original_price = %s, historical_sold = %s,
                        rating_average = %s, review_count = %s, status = %s, last_scraped_at = %s
                    WHERE id = %s
                    """,
                    (
                        data["current_price"],
                        data["original_price"],
                        data["historical_sold"],
                        data["rating_average"],
                        data["review_count"],
                        data["status"],
                        now,
                        link_id,
                    ),
                )
                cursor.execute(
                    "INSERT INTO price_history (link_id, price, scraped_at) VALUES (%s, %s, %s)",
                    (link_id, data["current_price"], now),
                )

                if data.get("thumbnail_url"):
                    cursor.execute(
                        "UPDATE products SET thumbnail_url = %s WHERE id = %s",
                        (data["thumbnail_url"], product_id),
                    )
                    print(f"[+] Da cap nhat thumbnail_url cho product ID: {product_id}")

                print(f"[+] Da cap nhat link ID: {link_id}\n")
            else:
                status = data.get("status", 3) if data else 3
                cursor.execute(
                    "UPDATE platform_links SET status = %s, last_scraped_at = %s WHERE id = %s",
                    (status, now, link_id),
                )
                print(f"[!] Bo qua link ID: {link_id} do loi cao du lieu.\n")

        conn.commit()
        print("[+] Da luu toan bo thay doi vao Database!")
    except mysql.connector.Error as err:
        print(f"[!] Loi MySQL: {err}")
    finally:
        if cursor:
            cursor.close()
        if conn and conn.is_connected():
            conn.close()
            print("[*] Da dong ket noi Database.")


if __name__ == "__main__":
    print("=== BAT DAU CAP NHAT GIA TIKI ===")
    update_tiki_prices_to_db()
    print("=== KET THUC CHUONG TRINH ===")
