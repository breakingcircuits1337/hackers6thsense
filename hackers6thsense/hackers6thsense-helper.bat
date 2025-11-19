@echo off
REM Hackers6thSense - Windows Helper Menu
REM Double-click to run or execute from command prompt

setlocal enabledelayedexpansion

:menu
cls
echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║                                                            ║
echo ║        🧠 HACKERS6THSENSE - HELPER MENU 🧠              ║
echo ║                                                            ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
echo 1. Start Development Server (Port 8000)
echo 2. Start Production Server
echo 3. Restart Server
echo 4. Stop All PHP Processes
echo 5. Reset Database
echo 6. View Server Logs
echo 7. Install Dependencies
echo 8. Check PHP Installation
echo 9. Test API Endpoints
echo 10. Open Dashboard
echo 11. View System Status
echo 12. Exit
echo.
set /p choice="Select option (1-12): "

if "%choice%"=="1" goto start_dev
if "%choice%"=="2" goto start_prod
if "%choice%"=="3" goto restart
if "%choice%"=="4" goto stop_php
if "%choice%"=="5" goto reset_db
if "%choice%"=="6" goto view_logs
if "%choice%"=="7" goto install_deps
if "%choice%"=="8" goto check_php
if "%choice%"=="9" goto test_api
if "%choice%"=="10" goto open_dashboard
if "%choice%"=="11" goto status
if "%choice%"=="12" goto exit_menu
goto menu

:start_dev
cls
echo.
echo Starting Development Server on Port 8000...
echo Access dashboard at: http://localhost:8000/main-dashboard.html
echo Press Ctrl+C to stop server
echo.
php -S localhost:8000 -t public
pause
goto menu

:start_prod
cls
echo.
echo Starting Production Server...
echo Access dashboard at: http://localhost:8000/main-dashboard.html
echo Press Ctrl+C to stop server
echo.
php -S 0.0.0.0:8000 -t public
pause
goto menu

:restart
cls
echo.
echo Stopping existing PHP processes...
taskkill /F /IM php.exe 2>nul
timeout /t 2 /nobreak
echo.
echo Starting development server...
php -S localhost:8000 -t public
pause
goto menu

:stop_php
cls
echo.
echo Stopping all PHP processes...
taskkill /F /IM php.exe 2>nul
if %errorlevel%==0 (
    echo PHP processes stopped.
) else (
    echo No PHP processes found or already stopped.
)
echo.
pause
goto menu

:reset_db
cls
echo.
set /p confirm="Reset database? This will delete all data. (y/n): "
if "%confirm%"=="y" (
    if exist databases\hackers6thsense.db (
        del databases\hackers6thsense.db
        echo Database deleted.
    ) else (
        echo Database file not found.
    )
    echo Restart server to recreate database.
)
echo.
pause
goto menu

:view_logs
cls
echo.
echo Recent logs from: logs\error.log
echo.
if exist logs\error.log (
    type logs\error.log
) else (
    echo No logs found yet.
)
echo.
pause
goto menu

:install_deps
cls
echo.
echo Installing PHP dependencies with Composer...
if exist composer.json (
    composer install
    echo Dependencies installed.
) else (
    echo composer.json not found.
)
echo.
pause
goto menu

:check_php
cls
echo.
echo Checking PHP installation...
echo.
php -v
echo.
echo PHP Location:
where php
echo.
pause
goto menu

:test_api
cls
echo.
echo Testing API endpoints...
echo.
echo Testing: GET /api/system/status
curl -X GET http://localhost:8000/api/system/status 2>nul
echo.
echo Testing: GET /api/threats
curl -X GET http://localhost:8000/api/threats 2>nul
echo.
pause
goto menu

:open_dashboard
cls
echo.
echo Opening dashboard in default browser...
start http://localhost:8000/main-dashboard.html
goto menu

:status
cls
echo.
echo System Status Information
echo ========================
echo.
echo Windows Version:
ver
echo.
echo PHP Version:
php -v 2>nul || echo PHP not found
echo.
echo Composer Status:
composer --version 2>nul || echo Composer not found
echo.
echo Current Directory:
cd
echo.
echo Project Files:
if exist public\main-dashboard.html echo [OK] main-dashboard.html
if exist public\css\main-dashboard.css echo [OK] main-dashboard.css
if exist public\js\main-dashboard.js echo [OK] main-dashboard.js
if exist .env echo [OK] .env configured
if exist databases echo [OK] databases directory
if exist logs echo [OK] logs directory
echo.
pause
goto menu

:exit_menu
echo.
echo Thank you for using Hackers6thSense!
echo.
exit /b 0
