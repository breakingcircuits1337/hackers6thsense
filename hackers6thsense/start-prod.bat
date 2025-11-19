@echo off
REM Hackers6thSense - Production Server Launcher

cls
echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║                                                            ║
echo ║        🧠 HACKERS6THSENSE - PRODUCTION SERVER 🧠        ║
echo ║                                                            ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
echo Starting PHP Production Server...
echo.
echo Server:    http://0.0.0.0:8000
echo Dashboard: http://localhost:8000/main-dashboard.html
echo.
echo Press Ctrl+C to stop the server
echo.

php -S 0.0.0.0:8000 -t public

echo.
echo Server stopped.
pause
