@echo off
REM Hackers6thSense - Server Restart Script

cls
echo.
echo ╔════════════════════════════════════════════════════════════╗
echo ║                                                            ║
echo ║        🧠 HACKERS6THSENSE - RESTARTING SERVER 🧠        ║
echo ║                                                            ║
echo ╚════════════════════════════════════════════════════════════╝
echo.
echo Stopping existing PHP processes...
taskkill /F /IM php.exe 2>nul
timeout /t 2 /nobreak
echo.
echo Starting development server...
call start-dev.bat
