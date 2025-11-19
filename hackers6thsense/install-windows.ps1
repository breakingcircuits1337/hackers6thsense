# Hackers6thSense - Automated Windows Installer
# Run as Administrator
# Usage: powershell -ExecutionPolicy Bypass -File install-windows.ps1

param(
    [switch]$SkipChecks = $false,
    [switch]$Development = $false,
    [switch]$Production = $false
)

# Color output functions
function Write-Status { Write-Host "✓ $args" -ForegroundColor Green }
function Write-Error { Write-Host "✗ $args" -ForegroundColor Red }
function Write-Info { Write-Host "ℹ $args" -ForegroundColor Cyan }
function Write-Warning { Write-Host "⚠ $args" -ForegroundColor Yellow }

# Check if running as administrator
function Test-Admin {
    $currentUser = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($currentUser)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

# Clear screen and show banner
Clear-Host
Write-Host @"
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║        🧠 HACKERS6THSENSE - WINDOWS INSTALLER 🧠         ║
║                                                            ║
║        AI-Powered Intelligent Network Defense             ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
"@ -ForegroundColor Magenta

Write-Info "Initializing installation process..."
Write-Host ""

# Check admin privileges
if (-not (Test-Admin)) {
    Write-Error "This script must run as Administrator!"
    Write-Info "Please run PowerShell as Administrator and try again."
    Start-Sleep -Seconds 3
    exit 1
}

Write-Status "Running as Administrator"

# ===== STEP 1: Check System Requirements =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 1: Checking System Requirements" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

# Check Windows version
$osVersion = [System.Environment]::OSVersion.Version
$isWindows10Plus = $osVersion.Major -ge 10

if ($isWindows10Plus) {
    Write-Status "Windows version: $($osVersion.Major).$($osVersion.Minor)"
} else {
    Write-Error "Windows 10 or later is required"
    exit 1
}

# Check available disk space
$disk = Get-PSDrive C | Select-Object @{Name="SpaceGB";Expression={[math]::Round($_.Free/1GB)}}
$requiredGB = 5

if ($disk.SpaceGB -ge $requiredGB) {
    Write-Status "Disk space: $($disk.SpaceGB)GB available (required: $($requiredGB)GB)"
} else {
    Write-Error "Insufficient disk space. Required: $($requiredGB)GB, Available: $($disk.SpaceGB)GB"
    exit 1
}

# ===== STEP 2: Check & Install Prerequisites =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 2: Checking Prerequisites" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

# Check PHP
Write-Info "Checking PHP..."
$phpExists = $null -ne (Get-Command php -ErrorAction SilentlyContinue)

if ($phpExists) {
    $phpVersion = php -v | Select-Object -First 1
    Write-Status "PHP detected: $phpVersion"
} else {
    Write-Warning "PHP not found in system PATH"
    Write-Info "Attempting to locate PHP installation..."
    
    $phpPaths = @(
        "C:\php",
        "C:\xampp\php",
        "C:\wamp64\bin\php",
        "${env:ProgramFiles}\PHP"
    )
    
    $phpFound = $false
    foreach ($path in $phpPaths) {
        if (Test-Path "$path\php.exe") {
            Write-Status "PHP found at: $path"
            $env:PATH += ";$path"
            $phpFound = $true
            break
        }
    }
    
    if (-not $phpFound) {
        Write-Error "PHP is not installed or not in PATH"
        Write-Info "Download PHP from: https://windows.php.net/download/"
        Write-Info "Or install XAMPP (includes PHP): https://www.apachefriends.org/"
        exit 1
    }
}

# Check Git
Write-Info "Checking Git..."
$gitExists = $null -ne (Get-Command git -ErrorAction SilentlyContinue)

if ($gitExists) {
    Write-Status "Git is installed"
} else {
    Write-Warning "Git not found"
    Write-Info "Install Git from: https://git-scm.com/download/win"
}

# Check Node.js (optional but recommended)
Write-Info "Checking Node.js..."
$nodeExists = $null -ne (Get-Command node -ErrorAction SilentlyContinue)

if ($nodeExists) {
    Write-Status "Node.js detected"
} else {
    Write-Warning "Node.js not installed (optional, but recommended)"
    Write-Info "Download from: https://nodejs.org/"
}

# ===== STEP 3: Project Setup =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 3: Setting Up Project" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

$projectPath = Split-Path -Path $PSCommandPath -Parent
Write-Info "Project path: $projectPath"
Write-Status "Found installation directory"

# ===== STEP 4: Create .env Configuration =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 4: Configuring Environment" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

$envFile = Join-Path $projectPath ".env"
$envExampleFile = Join-Path $projectPath ".env.example"

if (Test-Path $envFile) {
    Write-Warning ".env file already exists"
    $response = Read-Host "Overwrite existing .env? (y/n)"
    if ($response -ne "y") {
        Write-Info "Keeping existing .env file"
    } else {
        if (Test-Path $envExampleFile) {
            Copy-Item $envExampleFile $envFile -Force
            Write-Status "Created new .env from template"
        }
    }
} else {
    if (Test-Path $envExampleFile) {
        Copy-Item $envExampleFile $envFile
        Write-Status "Created .env from template"
    } else {
        Write-Warning ".env.example not found, creating basic .env"
        @"
APP_NAME=Hackers6thSense
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_TYPE=sqlite
DB_HOST=localhost
DB_NAME=hackers6thsense.db
DB_USER=
DB_PASSWORD=

# AI Provider Configuration
MISTRAL_API_KEY=your_mistral_key_here
GROQ_API_KEY=your_groq_key_here
GOOGLE_GEMINI_KEY=your_gemini_key_here

# Security
JWT_SECRET=$(Get-Random -Minimum 100000000 -Maximum 999999999)
ENCRYPTION_KEY=$(New-Guid)

# pfSense Integration
PFSENSE_HOST=localhost
PFSENSE_USER=admin
PFSENSE_PASSWORD=pfsense

# Logging
LOG_LEVEL=info
LOG_PATH=./logs

# Server
SERVER_PORT=8000
SERVER_HOST=0.0.0.0
"@ | Out-File $envFile -Encoding UTF8
        Write-Status "Created basic .env configuration"
    }
}

# ===== STEP 5: Create Directory Structure =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 5: Creating Directory Structure" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

$directories = @(
    "logs",
    "cache",
    "uploads",
    "databases",
    "reports"
)

foreach ($dir in $directories) {
    $dirPath = Join-Path $projectPath $dir
    if (-not (Test-Path $dirPath)) {
        New-Item -ItemType Directory -Path $dirPath -Force | Out-Null
        Write-Status "Created directory: $dir"
    } else {
        Write-Info "Directory already exists: $dir"
    }
}

# ===== STEP 6: Install PHP Dependencies =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 6: Installing PHP Dependencies" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

$composerFile = Join-Path $projectPath "composer.json"
if (Test-Path $composerFile) {
    Write-Info "Installing Composer dependencies..."
    
    # Check if composer is installed
    $composerExists = $null -ne (Get-Command composer -ErrorAction SilentlyContinue)
    
    if ($composerExists) {
        & composer install --quiet
        Write-Status "Composer dependencies installed"
    } else {
        Write-Warning "Composer not found, skipping PHP dependency installation"
        Write-Info "To install dependencies manually: composer install"
    }
} else {
    Write-Info "No composer.json found, skipping dependency installation"
}

# ===== STEP 7: Initialize Database =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 7: Initializing Database" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

$dbPath = Join-Path $projectPath "databases" "hackers6thsense.db"

if (Test-Path $dbPath) {
    Write-Info "Database already exists: $dbPath"
    $response = Read-Host "Reset database? (y/n)"
    if ($response -eq "y") {
        Remove-Item $dbPath -Force
        Write-Status "Database removed"
    }
}

if (-not (Test-Path $dbPath)) {
    Write-Info "Creating SQLite database..."
    Write-Status "Database will be initialized on first server run"
}

# ===== STEP 8: Set File Permissions =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 8: Setting File Permissions" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

$writeableDirs = @("logs", "cache", "uploads", "databases")

foreach ($dir in $writeableDirs) {
    $dirPath = Join-Path $projectPath $dir
    try {
        $acl = Get-Acl $dirPath
        $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule(
            "Everyone",
            "Modify",
            "ContainerInherit,ObjectInherit",
            "None",
            "Allow"
        )
        $acl.SetAccessRule($accessRule)
        Set-Acl $dirPath $acl
        Write-Status "Permissions set for: $dir"
    } catch {
        Write-Warning "Could not set permissions for $dir"
    }
}

# ===== STEP 9: Create Batch Files for Easy Launch =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 9: Creating Launch Scripts" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

# Create development batch file
$devBatchFile = Join-Path $projectPath "start-dev.bat"
@"
@echo off
echo.
echo 🧠 HACKERS6THSENSE - DEVELOPMENT SERVER 🧠
echo.
echo Starting PHP development server...
echo Server: http://localhost:8000
echo Dashboard: http://localhost:8000/main-dashboard.html
echo.
php -S 0.0.0.0:8000 -t public
"@ | Out-File $devBatchFile -Encoding ASCII -Force
Write-Status "Created: start-dev.bat"

# Create production batch file
$prodBatchFile = Join-Path $projectPath "start-prod.bat"
@"
@echo off
echo.
echo 🧠 HACKERS6THSENSE - PRODUCTION SERVER 🧠
echo.
echo Starting PHP server...
echo Server: http://0.0.0.0:8000
echo Dashboard: http://localhost:8000/main-dashboard.html
echo.
php -S 0.0.0.0:8000 -t public
"@ | Out-File $prodBatchFile -Encoding ASCII -Force
Write-Status "Created: start-prod.bat"

# Create restart batch file
$restartBatchFile = Join-Path $projectPath "restart.bat"
@"
@echo off
echo.
echo 🔄 Restarting Hackers6thSense...
echo.
taskkill /F /IM php.exe 2>nul
timeout /t 2 /nobreak
call start-dev.bat
"@ | Out-File $restartBatchFile -Encoding ASCII -Force
Write-Status "Created: restart.bat"

# Create PowerShell launch scripts
$devPsFile = Join-Path $projectPath "start-dev.ps1"
@"
# Hackers6thSense Development Server Launcher
Write-Host @"
╔════════════════════════════════════════════════════════════╗
║                                                            ║
║        🧠 HACKERS6THSENSE - DEVELOPMENT SERVER 🧠        ║
║                                                            ║
╚════════════════════════════════════════════════════════════╝
"@ -ForegroundColor Magenta

Write-Host "Starting PHP development server..." -ForegroundColor Cyan
Write-Host "Server: http://localhost:8000" -ForegroundColor Green
Write-Host "Dashboard: http://localhost:8000/main-dashboard.html" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop`n" -ForegroundColor Yellow

php -S 0.0.0.0:8000 -t public
"@ | Out-File $devPsFile -Encoding UTF8 -Force
Write-Status "Created: start-dev.ps1"

# ===== STEP 10: Generate Test/Demo Data =====
Write-Host "`n" + ("="*60) -ForegroundColor Cyan
Write-Host "STEP 10: Installation Summary" -ForegroundColor Cyan
Write-Host ("="*60) -ForegroundColor Cyan

Write-Host @"

✅ HACKERS6THSENSE INSTALLATION COMPLETE!

📍 Installation Location:
   $projectPath

🚀 QUICK START:

   Option 1 - Using Batch File (Easiest):
   1. Open File Explorer
   2. Navigate to: $projectPath
   3. Double-click: start-dev.bat
   4. Open browser: http://localhost:8000

   Option 2 - Using PowerShell:
   1. Open PowerShell
   2. Navigate to: cd $projectPath
   3. Run: php -S localhost:8000 -t public
   4. Open browser: http://localhost:8000

   Option 3 - Using PowerShell Script:
   1. Open PowerShell
   2. Run: .\start-dev.ps1
   3. Open browser: http://localhost:8000

📊 DASHBOARD ACCESS:
   URL: http://localhost:8000/main-dashboard.html
   Default: No authentication required for demo

📝 IMPORTANT FILES:
   .env              - Configuration file (edit with your API keys)
   start-dev.bat     - Quick development server launcher
   start-prod.bat    - Production server launcher
   restart.bat       - Restart the server
   logs/             - Server and application logs
   databases/        - SQLite database storage

🔧 CONFIGURATION:
   Edit .env file with:
   - MISTRAL_API_KEY (optional)
   - GROQ_API_KEY (optional)
   - GOOGLE_GEMINI_KEY (optional)
   - Database settings

💡 FIRST STEPS:
   1. Start the server (see QUICK START above)
   2. Open http://localhost:8000/main-dashboard.html
   3. Go to "Attacks" tab
   4. Click "Execute" on any attack simulation
   5. Check "Overview" tab for results

🆘 TROUBLESHOOTING:
   • Port 8000 already in use?
     → Kill: taskkill /F /IM php.exe
     → Or edit start-dev.bat and change 8000 to 8001

   • PHP not found?
     → Ensure PHP is in your PATH
     → Or edit start-dev.bat with full PHP path

   • Database error?
     → Delete databases/hackers6thsense.db
     → Restart the server (will recreate)

   • Permission denied?
     → Run PowerShell as Administrator
     → Check logs/ folder permissions

📚 DOCUMENTATION:
   - README.md - Project overview
   - DOCUMENTATION_INDEX.md - All documentation
   - OBLIVION_INTEGRATION.md - Attack framework
   - API_QUICK_REFERENCE.md - API reference

🎉 Ready to secure your network! Enjoy Hackers6thSense!

"@ -ForegroundColor Green

# Offer to start server
Write-Host ""
$response = Read-Host "Start server now? (y/n)"

if ($response -eq "y") {
    Write-Host "`nStarting development server..." -ForegroundColor Cyan
    Write-Host "Press Ctrl+C to stop`n" -ForegroundColor Yellow
    
    Set-Location $projectPath
    & php -S 0.0.0.0:8000 -t public
} else {
    Write-Host "`nTo start server later, run:" -ForegroundColor Yellow
    Write-Host "  $devBatchFile" -ForegroundColor Cyan
    Write-Host "`nOr from PowerShell:" -ForegroundColor Yellow
    Write-Host "  cd $projectPath" -ForegroundColor Cyan
    Write-Host "  php -S localhost:8000 -t public" -ForegroundColor Cyan
}
