@echo off
setlocal
chcp 65001 >nul

cd /d "%~dp0.."
if not exist "storage\bot_logs" mkdir "storage\bot_logs"

set "PYTHON_BIN=C:\Users\ASUS-PRO\AppData\Local\Python\bin\python.exe"
set "PYTHONUTF8=1"
set "LAZADA_STOP_ON_CAPTCHA=true"
set "LAZADA_ALLOW_MANUAL_CLEAR=false"
set "LOG_FILE=storage\bot_logs\lazada_scheduled.log"
set "FLAG_FILE=storage\bot_logs\lazada_task_disabled.flag"

echo.>> "%LOG_FILE%"
echo ==== %DATE% %TIME% - Lazada scheduled run ====>> "%LOG_FILE%"
%PYTHON_BIN% crawlers\lazada_crawler.py >> "%LOG_FILE%" 2>&1
set "EXIT_CODE=%ERRORLEVEL%"
echo ==== Exit code: %EXIT_CODE% ====>> "%LOG_FILE%"

if "%EXIT_CODE%"=="2" (
    echo ==== Captcha/block detected. Disabling Lazada scheduled task ====>> "%LOG_FILE%"
    (
        echo platform=Lazada
        echo task=SmartPrice Lazada Crawler
        echo disabled_at=%DATE% %TIME%
        echo reason=Captcha/block detected by scheduled crawler
        echo action=Run Lazada manually, handle verification, then enable the task again
    ) > "%FLAG_FILE%"
    schtasks /Change /TN "SmartPrice Lazada Crawler" /DISABLE >> "%LOG_FILE%" 2>&1
)

if "%EXIT_CODE%"=="0" (
    if exist "%FLAG_FILE%" del "%FLAG_FILE%" >nul 2>&1
)

exit /b %EXIT_CODE%
