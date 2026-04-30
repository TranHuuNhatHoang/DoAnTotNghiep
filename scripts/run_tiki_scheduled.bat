@echo off
setlocal

cd /d "%~dp0.."
if not exist "storage\bot_logs" mkdir "storage\bot_logs"

set "PYTHON_BIN=python"
set "LOG_FILE=storage\bot_logs\tiki_scheduled.log"

echo.>> "%LOG_FILE%"
echo ==== %DATE% %TIME% | Tiki scheduled run ====>> "%LOG_FILE%"
%PYTHON_BIN% tiki_scraper.py >> "%LOG_FILE%" 2>&1
set "EXIT_CODE=%ERRORLEVEL%"
echo ==== Exit code: %EXIT_CODE% ====>> "%LOG_FILE%"

exit /b %EXIT_CODE%
