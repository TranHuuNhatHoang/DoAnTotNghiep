@echo off
setlocal
chcp 65001 >nul

cd /d "%~dp0.."
if not exist "storage\bot_logs" mkdir "storage\bot_logs"

set "PHP_BIN=php"
set "LOG_FILE=storage\bot_logs\price_alerts_scheduled.log"

echo.>> "%LOG_FILE%"
echo ==== %DATE% %TIME% - Price alerts scheduled run ====>> "%LOG_FILE%"
%PHP_BIN% cron_send_alerts.php >> "%LOG_FILE%" 2>&1
set "EXIT_CODE=%ERRORLEVEL%"
echo ==== Exit code: %EXIT_CODE% ====>> "%LOG_FILE%"

exit /b %EXIT_CODE%
