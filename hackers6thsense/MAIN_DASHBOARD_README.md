# Hackers6thSense - Main Dashboard

## Overview

**Hackers6thSense** is a comprehensive, unified GUI dashboard that brings together all components of the integrated security platform:
- 🔥 **pfSense Firewall** management
- 🧠 **AI-Powered** threat detection and analysis
- ⚙️ **Oblivion Framework** attack simulations
- 🛡️ **LEGION** threat intelligence integration
- 🤖 **Autonomous Agents** for continuous security

## Features

### 🎯 System Overview
- Real-time metrics dashboard
- System status monitoring
- AI provider status
- Quick action buttons
- 24-hour threat timeline
- Attack distribution visualization

### 🛡️ Threat Management
- Real-time threat scanning
- Threat severity filtering
- Detailed threat reports
- Export capabilities
- Integration with LEGION threat intelligence

### 📊 Network Traffic Analysis
- Traffic analysis over multiple timeframes
- Inbound/Outbound metrics
- Anomaly detection
- Traffic pattern visualization
- Historical data

### 🎮 Attack Simulations (Oblivion Framework)
- **DDoS Simulation** - Test DDoS protection
- **SQL Injection** - Database vulnerability testing
- **Brute Force** - Authentication security testing
- **Ransomware Simulation** - Encryption/backup testing
- **Phishing Campaigns** - User security awareness
- **AI Attack Planning** - Mistral AI-generated attack plans

### ⚙️ Configuration Management
- Firewall rule review
- Configuration analysis
- System recommendations
- Integration status monitoring
- Real-time compliance checking

### 🧠 Threat Intelligence
- Recent threats dashboard
- Vulnerability reporting
- AI recommendations
- Threat correlation
- Intelligence export

### 💬 AI Assistant Chat
- Natural language queries
- Network context awareness
- Security recommendations
- Multi-turn conversations
- AI provider: Mistral, Groq, or Google Gemini

### 🤖 Autonomous Agents
- Agent management interface
- Real-time agent monitoring
- Agent log viewing
- Agent task execution
- Performance metrics

### ⚙️ System Settings
- Dashboard preferences
- Alert configuration
- Notification settings
- AI provider selection
- Theme preferences

## Architecture

```
┌─────────────────────────────────────────────────┐
│          Hackers6thSense Main Dashboard         │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │ Sidebar Navigation (9 Sections)          │  │
│  │ • Overview    • Threats    • Traffic     │  │
│  │ • Attacks     • Config     • Intelligence│  │
│  │ • Chat        • Agents     • Settings    │  │
│  └──────────────────────────────────────────┘  │
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │ Header (Search, Notifications, Profile) │  │
│  └──────────────────────────────────────────┘  │
│                                                 │
│  ┌──────────────────────────────────────────┐  │
│  │ Main Content Area (Dynamic Sections)     │  │
│  │ • Metrics cards                          │  │
│  │ • Status panels                          │  │
│  │ • Charts & visualizations                │  │
│  │ • Action controls                        │  │
│  └──────────────────────────────────────────┘  │
└─────────────────────────────────────────────────┘
         ↓
    ┌────────────────────────┐
    │   REST API Backend     │
    ├────────────────────────┤
    │ • pfSense Integration  │
    │ • Oblivion Framework   │
    │ • LEGION Intelligence  │
    │ • AI Engines           │
    │ • Database Storage     │
    └────────────────────────┘
```

## File Structure

```
public/
├── main-dashboard.html       # Main dashboard HTML (NEW)
├── css/
│   └── main-dashboard.css    # Dashboard styling (NEW)
├── js/
│   └── main-dashboard.js     # Dashboard functionality (NEW)
├── dashboard.html            # Legacy dashboard (redirects to main)
└── index.php                 # Legacy index (redirects to main)
```

## Sections Explained

### 📈 Overview
The central hub showing:
- **Metrics Cards**: Critical threats, network health, active agents, blocked threats
- **System Status**: Real-time status of all integrated systems
- **Top Threats**: Active threats requiring attention
- **AI Providers**: Status of connected AI services
- **Quick Actions**: Buttons to common tasks

### 🚨 Threats
Comprehensive threat management including:
- Real-time threat list
- Severity-based filtering
- Threat details and timeline
- Export and reporting
- Integration with LEGION

### 📡 Traffic
Network traffic analysis showing:
- Total, inbound, outbound traffic metrics
- Anomaly detection
- Traffic patterns and trends
- Historical comparisons
- Time-based filtering

### ⚔️ Attacks
Red team and attack simulation center with:
- **6 Attack Modules** ready to execute
- Difficulty levels (Medium to Critical)
- Execution controls
- Simulation history
- Results tracking

Attack types:
1. DDoS Simulation - Network flooding
2. SQL Injection - Database attacks
3. Brute Force - Authentication tests
4. Ransomware Simulation - Encryption tests
5. Phishing Campaign - Social engineering
6. AI Attack Plan - Custom AI-generated plans

### ⚙️ Configuration
System configuration tools:
- Firewall rules viewer
- Configuration analyzer
- Recommendation engine
- Integration status dashboard
- Policy compliance checker

### 🧠 Intelligence
Threat intelligence aggregation:
- Consolidated threat view
- Vulnerability database
- AI recommendations
- Threat correlation
- Export capabilities

### 💬 Chat
AI-powered assistant for:
- Natural language queries
- Network context
- Security guidance
- Multi-turn conversations
- Real-time analysis

### 🤖 Agents
Autonomous agent management:
- List all running agents
- Start new agents
- Monitor agent activity
- View agent logs
- Track performance

### ⚙️ Settings
Configuration and preferences:
- System settings
- Alert preferences
- AI provider selection
- Notification options
- Theme preferences

## Getting Started

### Access the Dashboard
```
http://localhost:8000/main-dashboard.html
```

Or simply access:
```
http://localhost:8000/dashboard.html    (auto-redirects)
http://localhost:8000/index.php         (auto-redirects)
```

### First Time Setup

1. **Verify System Status**
   - Go to Overview tab
   - Check all systems are online

2. **Configure AI Provider**
   - Go to Settings
   - Select your AI provider
   - Enter API credentials

3. **Run Initial Scan**
   - Go to Threats tab
   - Click "Run Threat Scan"
   - Review results

4. **Explore Simulations**
   - Go to Attacks tab
   - Review available attack modules
   - Execute a test simulation

## API Endpoints Used

The dashboard connects to 60+ API endpoints:

### Core Endpoints
- `GET /api/system/status` - System status
- `GET /api/system/providers` - AI providers
- `GET /api/threats` - Threats list
- `POST /api/threats/analyze` - Run threat scan
- `GET /api/threats/dashboard` - Threat dashboard

### Traffic
- `POST /api/analysis/traffic` - Analyze traffic
- `GET /api/analysis/anomalies` - Detect anomalies

### Configuration
- `GET /api/config/rules` - Get firewall rules
- `POST /api/config/analyze` - Analyze configuration
- `GET /api/recommendations` - Get recommendations

### Oblivion (Attack Simulations)
- `POST /api/oblivion/session/start` - Start session
- `POST /api/oblivion/plan` - Generate attack plan
- `POST /api/oblivion/attack/ddos` - DDoS simulation
- `POST /api/oblivion/attack/sqli` - SQL injection
- `POST /api/oblivion/attack/bruteforce` - Brute force
- `POST /api/oblivion/attack/ransomware` - Ransomware
- `POST /api/oblivion/phishing/generate` - Phishing
- `GET /api/oblivion/statistics` - Attack statistics

### Chat & Agents
- `POST /api/chat` - Send chat message
- `GET /api/agents` - List agents
- `POST /api/agents` - Start new agent

## Customization

### Change Colors
Edit `/public/css/main-dashboard.css` CSS variables:
```css
:root {
    --primary: #667eea;
    --secondary: #764ba2;
    --accent: #f093fb;
    --danger: #d32f2f;
    --warning: #f57c00;
    --success: #388e3c;
}
```

### Add New Sections
1. Add HTML section in `main-dashboard.html`
2. Add navigation item in sidebar
3. Create load function in `main-dashboard.js`
4. Add CSS styling in `main-dashboard.css`

### Modify Attack Modules
Edit attack cards in `main-dashboard.html` under Attacks section or modify `executeAttack()` function in `main-dashboard.js`.

## Responsive Design

Dashboard is fully responsive:
- **Desktop** (> 1024px) - Full sidebar, multi-column layout
- **Tablet** (768px - 1024px) - Collapsible sidebar
- **Mobile** (< 768px) - Mobile menu, single column layout

## Performance Tips

1. **Dashboard Loading**
   - Uses async/await for API calls
   - Implements caching where possible
   - Lazy loads section data on navigation

2. **Real-time Updates**
   - Configure auto-refresh in Settings
   - Manual refresh available on each panel
   - Real-time threat notifications

3. **Browser Support**
   - Chrome/Chromium 90+
   - Firefox 88+
   - Safari 14+
   - Edge 90+

## Troubleshooting

### Dashboard Not Loading
1. Check if API service is running
2. Verify browser console for errors
3. Clear browser cache (Ctrl+Shift+Delete)
4. Check `/var/log/pfsense-ai-manager/error.log`

### API Calls Failing
1. Verify API endpoints are accessible
2. Check authentication tokens
3. Review CORS settings
4. Check network connectivity

### Threat Scan Not Working
1. Ensure Oblivion framework is running
2. Verify policy file exists and is valid
3. Check firewall rules allow simulation traffic
4. Review Oblivion logs

## Security Notes

- ✅ All data transmitted over HTTPS in production
- ✅ API authentication via bearer tokens
- ✅ CSRF protection enabled
- ✅ Input validation on all forms
- ✅ XSS prevention via HTML escaping
- ✅ Audit logging for all actions

## Browser Console Commands

For advanced users, useful console commands:

```javascript
// Reload section data
loadSectionData('overview');

// Manually trigger notification
showNotification('Test message', 'success');

// View API response
navigateTo('threats');
```

## Support & Documentation

- **Integration Docs**: See `OBLIVION_INTEGRATION.md`
- **API Reference**: See `API_QUICK_REFERENCE.md`
- **Security Guide**: See `SECURITY.md`
- **Configuration**: See `COMPLETE_SETUP_GUIDE.md`

## Version Information

- **Dashboard Version**: 1.0.0
- **Build Date**: January 2024
- **API Compatibility**: v1
- **Framework**: Vanilla HTML/CSS/JavaScript (no dependencies)
- **File Size**: ~200KB (uncompressed)

## Future Enhancements

- 📊 Real-time charts using Chart.js
- 🎨 Dark mode implementation
- 📱 Mobile app version
- 🔔 Desktop notifications
- 📈 Advanced analytics
- 🌐 Multi-language support
- 🔐 Two-factor authentication
- 📊 Custom dashboard widgets

## Credits

Built as part of the Hackers6thSense security platform, integrating:
- pfSense firewall management
- Oblivion cyber range framework
- LEGION threat intelligence
- Mistral, Groq, and Google Gemini AI

---

**Status**: Production Ready ✅  
**Last Updated**: January 15, 2024  
**Maintained By**: Security Team
