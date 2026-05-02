@echo off
title He Thong Tu Dong Cap Nhat Gia - Do An Tot Nghiep
color 0A

echo ===================================================
echo CHUONG TRINH TU DONG THU THAP DU LIEU LUC NUA DEM
echo ===================================================

:: Di chuyển vào đúng thư mục dự án
cd C:\xampp\htdocs\DoAnTotNghiep

echo.
echo [1/3] Dang khoi dong Bot Tiki...
python crawlers\tiki_scraper.py

:: Tạm nghỉ 5 giây cho hệ thống xả RAM
timeout /t 5 /nobreak > NUL

echo.
echo [2/3] Dang khoi dong Bot Shopee...
python crawlers\shopee_crawler.py

timeout /t 5 /nobreak > NUL

echo.
echo [3/3] Dang khoi dong Bot Lazada...
python crawlers\lazada_crawler.py

echo.
echo ===================================================
echo HOAN THANH CAP NHAT DU LIEU TOAN BO HE THONG!
echo ===================================================
timeout /t 10
echo.
echo [4/4] Kich hoat Bot PHP quet canh bao gia va gui Email...
php cron_send_alerts.php
