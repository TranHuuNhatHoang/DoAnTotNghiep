@echo off
setlocal

for %%I in ("%~dp0..") do set "PROJECT_DIR=%%~fI"

schtasks /Create /TN "SmartPrice Shopee Crawler" /SC MINUTE /MO 15 /TR "\"%PROJECT_DIR%\scripts\run_shopee_scheduled.bat\"" /F
schtasks /Create /TN "SmartPrice Tiki Crawler" /SC HOURLY /MO 1 /TR "\"%PROJECT_DIR%\scripts\run_tiki_scheduled.bat\"" /F
schtasks /Create /TN "SmartPrice Lazada Crawler" /SC HOURLY /MO 2 /TR "\"%PROJECT_DIR%\scripts\run_lazada_scheduled.bat\"" /F

echo.
echo Da tao lich chay bot SmartPrice.
echo Shopee: moi 15 phut
echo Tiki: moi 1 gio
echo Lazada: moi 2 gio

endlocal
