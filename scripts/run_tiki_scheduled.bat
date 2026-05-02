@echo off
setlocal
chcp 65001 >nul

cd /d "%~dp0.."
if not exist "storage\bot_logs" mkdir "storage\bot_logs"

set "PYTHON_BIN=C:\Users\ASUS-PRO\AppData\Local\Python\bin\python.exe"
set "PYTHONUTF8=1"
set "LOG_FILE=storage\bot_logs\tiki_scheduled.log"

echo.>> "%LOG_FILE%"
echo ==== %DATE% %TIME% - Tiki scheduled run ====>> "%LOG_FILE%"
%PYTHON_BIN% crawlers\tiki_scraper.py >> "%LOG_FILE%" 2>&1
set "EXIT_CODE=%ERRORLEVEL%"
echo ==== Exit code: %EXIT_CODE% ====>> "%LOG_FILE%"

exit /b %EXIT_CODE%
