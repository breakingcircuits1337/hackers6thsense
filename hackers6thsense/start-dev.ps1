# Hackers6thSense - PowerShell Development Server Launcher
# Right-click and select "Run with PowerShell"

Clear-Host

Write-Host @"
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║        🧠 HACKERS6THSENSE - DEVELOPMENT SERVER 🧠        ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
"@ -ForegroundColor Magenta

Write-Host ""
Write-Host "Starting PHP Development Server..." -ForegroundColor Cyan
Write-Host ""
Write-Host "Server:    " -NoNewline -ForegroundColor Yellow
Write-Host "http://localhost:8000" -ForegroundColor Green
Write-Host "Dashboard: " -NoNewline -ForegroundColor Yellow
Write-Host "http://localhost:8000/main-dashboard.html" -ForegroundColor Green
Write-Host ""
Write-Host "Press " -NoNewline -ForegroundColor Yellow
Write-Host "Ctrl+C" -NoNewline -ForegroundColor Red
Write-Host " to stop the server" -ForegroundColor Yellow
Write-Host ""

php -S 0.0.0.0:8000 -t public

Write-Host ""
Write-Host "Server stopped." -ForegroundColor Yellow
Read-Host "Press Enter to exit"
