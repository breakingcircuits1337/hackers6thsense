# 🚀 Hackers6thSense - Installation & Setup Guide

## ⚡ Quick Start (2 Minutes)

### Option 1: Automated Installer (Recommended)

**Step 1: Open PowerShell as Administrator**
- Press `Windows Key + X`
- Click `Windows PowerShell (Admin)`

**Step 2: Run the installer**
```powershell
Set-ExecutionPolicy -ExecutionPolicy Bypass -Scope Process -Force
.\install-windows.ps1
```

**Step 3: Follow the prompts**
The installer will:
- ✓ Check system requirements
- ✓ Create directories
- ✓ Set up .env file
- ✓ Initialize database
- ✓ Create launch scripts

**Step 4: Start the server**
- Choose "y" when prompted
- Or manually run `start-dev.bat`

**Step 5: Open Dashboard**
- Browser: `http://localhost:8000/main-dashboard.html`

---

### Option 2: Double-Click Launch (Easiest)

After installation, simply:
1. Open File Explorer
2. Navigate to: `pfsense-ai-manager/`
3. Double-click: `start-dev.bat`
4. Open browser: `http://localhost:8000`

---

### Option 3: Manual Command

```powershell
cd pfsense-ai-manager
php -S localhost:8000 -t public
```

---

## 📋 System Requirements

✅ **Windows 10** or later  
✅ **PHP 7.4** or later  
✅ **5GB** free disk space  
✅ **Internet connection** (optional, for AI APIs)  
✅ **Administrator access** (for installer)

---

## 📁 Installation Files

The installer creates/uses these files:

| File | Purpose |
|------|---------|
| `install-windows.ps1` | Automated installer |
| `start-dev.bat` | Quick launch development server |
| `start-prod.bat` | Launch production server |
| `start-dev.ps1` | PowerShell version of launcher |
| `restart.bat` | Restart the server |
| `hackers6thsense-helper.bat` | Interactive helper menu |
| `.env` | Configuration file |
| `logs/` | Server logs |
| `databases/` | SQLite database |
| `cache/` | Application cache |
| `uploads/` | File uploads |

---

## 🔧 Configuration

### Edit .env File

After installation, edit `.env` to add:

```
# AI Provider Keys (Optional)
MISTRAL_API_KEY=your_key_here
GROQ_API_KEY=your_key_here
GOOGLE_GEMINI_KEY=your_key_here

# pfSense Firewall Integration
PFSENSE_HOST=192.168.1.1
PFSENSE_USER=admin
PFSENSE_PASSWORD=your_password

# Database (SQLite by default)
DB_TYPE=sqlite
DB_NAME=hackers6thsense.db

# Logging
LOG_LEVEL=info
LOG_PATH=./logs
```

---

## 🚀 Usage Guide

### Start Server

**Method 1: Batch File (Easiest)**
- Double-click: `start-dev.bat`

**Method 2: Command Line**
```powershell
cd pfsense-ai-manager
php -S localhost:8000 -t public
```

**Method 3: PowerShell Script**
```powershell
.\start-dev.ps1
```

**Method 4: Helper Menu**
- Double-click: `hackers6thsense-helper.bat`
- Choose option 1

### Stop Server
- Press `Ctrl+C` in terminal
- Or use: `taskkill /F /IM php.exe`
- Or double-click: `restart.bat`

### Access Dashboard
- Open browser
- Visit: `http://localhost:8000/main-dashboard.html`

---

## 📊 Helper Menu Options

Double-click `hackers6thsense-helper.bat` for:

| Option | Description |
|--------|-------------|
| 1 | Start Development Server (Port 8000) |
| 2 | Start Production Server |
| 3 | Restart Server |
| 4 | Stop All PHP Processes |
| 5 | Reset Database |
| 6 | View Server Logs |
| 7 | Install Dependencies |
| 8 | Check PHP Installation |
| 9 | Test API Endpoints |
| 10 | Open Dashboard |
| 11 | View System Status |
| 12 | Exit |

---

## 🧪 Testing

### Verify Installation

1. **Check PHP**
   ```powershell
   php -v
   ```

2. **Start Server**
   ```powershell
   php -S localhost:8000 -t public
   ```

3. **Open Dashboard**
   - Browser: `http://localhost:8000/main-dashboard.html`

4. **Test Attack Simulation**
   - Go to: Attacks tab
   - Click: Execute on any attack
   - Check: Results appear

5. **Test AI Chat**
   - Go to: AI Chat tab
   - Ask: "What is a DDoS attack?"
   - Check: AI responds

---

## 🐛 Troubleshooting

### PHP Not Found

**Problem:** `php: The term 'php' is not recognized`

**Solution:**
1. Install XAMPP (includes PHP)
   - Download: https://www.apachefriends.org/
2. Add PHP to PATH
   ```powershell
   $env:PATH += ";C:\xampp\php"
   ```
3. Verify
   ```powershell
   php -v
   ```

### Port Already In Use

**Problem:** `Address already in use`

**Solution:**
```powershell
# Kill existing PHP process
taskkill /F /IM php.exe

# Or use different port
php -S localhost:8001 -t public
```

### Database Error

**Problem:** Database connection error

**Solution:**
```powershell
# Delete database
Remove-Item databases/hackers6thsense.db

# Restart server (recreates DB automatically)
php -S localhost:8000 -t public
```

### Dashboard Won't Load

**Problem:** 404 or blank page

**Solution:**
1. Check correct URL: `http://localhost:8000/main-dashboard.html`
2. Check file exists: `public/main-dashboard.html`
3. Check browser console: F12 → Console tab
4. Check server logs: `logs/error.log`

### Permission Denied

**Problem:** Cannot write to logs or database

**Solution:**
```powershell
# Run PowerShell as Administrator
# Retry installer with admin privileges
```

---

## 📈 First Steps

After successful installation:

1. **Explore Dashboard** (10 min)
   - Click through 13 tabs
   - Review metrics and status

2. **Run Attack Simulation** (5 min)
   - Go to Attacks tab
   - Execute DDoS or SQL injection
   - Check results in Logs

3. **Try AI Chat** (5 min)
   - Go to AI Chat tab
   - Ask security questions
   - See AI responses

4. **Create Schedule** (5 min)
   - Go to Schedules tab
   - Create "Daily Threat Scan"
   - Set frequency to daily

5. **Configure Settings** (10 min)
   - Go to Settings tab
   - Enable notifications
   - Select AI provider
   - Set preferences

---

## 📚 Documentation

- **README.md** - Project overview
- **BEGINNER'S_GUIDE.md** - Detailed explanation
- **DOCUMENTATION_INDEX.md** - All documentation
- **API_QUICK_REFERENCE.md** - API endpoints
- **OBLIVION_INTEGRATION.md** - Attack framework

---

## 🔐 Security Notes

### Important for Production

1. **Change Default Credentials**
   - Edit .env with strong passwords

2. **Enable HTTPS**
   - Use SSL certificates
   - Configure firewall rules

3. **Enable Authentication**
   - Set up user accounts
   - Configure JWT tokens

4. **Secure API Keys**
   - Use environment variables
   - Never commit .env to version control

5. **Enable Audit Logging**
   - Check logs for all activities
   - Monitor access patterns

---

## 📞 Support

### Getting Help

1. **Check Documentation**
   - Review DOCUMENTATION_INDEX.md
   - Read relevant guide

2. **View Logs**
   - Check `logs/error.log`
   - Look for error messages

3. **Browser Console**
   - Press F12
   - Check Console tab
   - Look for JavaScript errors

4. **Try Helper Menu**
   - Double-click `hackers6thsense-helper.bat`
   - Use diagnostic options

---

## ✅ Verification Checklist

Installation successful when:

- [ ] Installer runs without errors
- [ ] .env file created
- [ ] Directories created (logs, databases, cache)
- [ ] File permissions set
- [ ] Launch scripts created
- [ ] Server starts on port 8000
- [ ] Dashboard loads at http://localhost:8000
- [ ] No JavaScript errors in console
- [ ] All 13 tabs accessible
- [ ] Attack simulations execute

---

## 🎉 Ready to Use!

Hackers6thSense is now installed and ready to secure your network.

**To start:**
```
Double-click: start-dev.bat
Or: php -S localhost:8000 -t public
```

**To access:**
```
http://localhost:8000/main-dashboard.html
```

**Happy defending! 🛡️🧠**

---

*Version: 1.0.0*  
*Last Updated: November 18, 2025*  
*Status: Production Ready*
