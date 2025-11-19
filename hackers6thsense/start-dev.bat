@echo off
REM Hackers6thSense - Quick Development Server Launcher
REM Double-click this file to start the server

cls
echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║                                                            ║
echo ║        🧠 HACKERS6THSENSE - DEVELOPMENT SERVER 🧠       ║
echo ║                                                            ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
echo Starting PHP Development Server...
echo.
echo Server:    http://localhost:8000
echo Dashboard: http://localhost:8000/main-dashboard.html
echo.
echo Press Ctrl+C to stop the server
echo.

php -S localhost:8000 -t public

echo.
echo Server stopped.
pause
