# 🚀 Deployment Verification & Setup Guide

## ✅ Deployment Status: COMPLETE

All core code files have been successfully created and integrated into the Hackers6thSense project. The system is ready for deployment with comprehensive agent orchestration, scheduling, and filtering capabilities.

---

## 📋 Files Created/Modified

### Database Layer (2 files)
- ✅ `src/Database/Migration.php` - Schema creation for 5 tables
- ✅ `src/Database/Database.php` - PDO abstraction layer

### Agent Management (3 files)
- ✅ `src/Agents/AgentOrchestrator.php` - 50 agents across 8 categories (1500+ lines)
- ✅ `src/Agents/AgentScheduler.php` - Recurring job scheduling engine
- ✅ `src/Agents/FilterManager.php` - Advanced filtering system (8 filter types)

### API Endpoints (2 files)
- ✅ `src/API/Endpoints/AgentEndpoint.php` - 8 agent management routes
- ✅ `src/API/Endpoints/ScheduleEndpoint.php` - 11 schedule/filter routes

### Configuration & Utilities (2 files)
- ✅ `src/Utils/DatabaseConfig.php` - Multi-database configuration
- ✅ `.env.example` - Updated with 50+ new configuration options

### Setup & Deployment (2 files)
- ✅ `scheduler-task.php` - Cron/Windows Task Scheduler runner
- ✅ `install.php` - Automated installation and verification script

### Web Dashboards (2 files)
- ✅ `public/agents-dashboard.html` - Agent control interface (500+ lines)
- ✅ `public/scheduler-dashboard.html` - Schedule management UI

### Framework Integration (1 file)
- ✅ `src/API/Router.php` - Updated with 19 new API routes

### Bootstrap (1 file)
- ✅ `src/bootstrap.php` - Database initialization added

**Total: 16 files created/modified**

---

## 🏗️ System Architecture

### Database Schema (5 Tables)
```
agents
├── id (PK)
├── agent_id (unique)
├── name
├── category
├── description
├── status
└── metadata

schedules
├── id (PK)
├── agent_id (FK)
├── frequency
├── config (JSON)
├── is_active
├── last_execution
└── next_execution

execution_history
├── id (PK)
├── agent_id (FK)
├── status
├── config (JSON)
├── result (JSON)
├── started_at
└── completed_at

agent_results
├── id (PK)
├── execution_id (FK)
├── agent_id (FK)
├── result_type
├── severity
├── data (JSON)
└── created_at

filters
├── id (PK)
├── name
├── type
├── conditions (JSON)
├── is_active
└── created_at
```

### Agent Categories (50 Total)
- **Reconnaissance** (8 agents): Network scanning, enumeration, intelligence
- **Exploitation** (12 agents): Vulnerability exploitation, attack automation
- **Persistence** (7 agents): Backdoors, scheduled tasks, rootkits
- **Privilege Escalation** (6 agents): Kernel exploits, UAC bypass
- **Defense Evasion** (8 agents): AV evasion, IDS bypass, obfuscation
- **Command Execution** (5 agents): Shell, PowerShell, scripting
- **Data Exfiltration** (4 agents): DNS tunneling, HTTP, covert channels
- **Lateral Movement** (2 agents): PsExec, SSH pivoting

### API Endpoints (19 Total)

**Agent Management (8 endpoints)**
```
GET    /api/agents                    # List all agents
GET    /api/agents/:id                # Get agent details
POST   /api/agents/:id/execute        # Execute single agent
POST   /api/agents/batch/execute      # Execute multiple agents
GET    /api/agents/:id/results        # Get execution results
GET    /api/agents/active             # List active agents
POST   /api/agents/:id/stop           # Stop agent
GET    /api/agents/stats              # Get statistics
```

**Schedule Management (8 endpoints)**
```
POST   /api/schedules                 # Create schedule
GET    /api/schedules                 # List schedules
GET    /api/schedules/:id             # Get schedule
PUT    /api/schedules/:id             # Update schedule
DELETE /api/schedules/:id             # Delete schedule
GET    /api/schedules/history         # Execution history
POST   /api/schedules/execute         # Run scheduled jobs
GET    /api/schedules/stats           # Get statistics
```

**Filter Management (4 endpoints)**
```
POST   /api/filters                   # Create filter
GET    /api/filters                   # List filters
POST   /api/filters/apply             # Apply filters
DELETE /api/filters/:id               # Delete filter
```

---

## 📦 Installation Steps

### Step 1: Verify PHP Environment
```bash
php install.php
```

This script checks:
- PHP version (8.0+)
- Directory structure
- File permissions
- Dependencies
- Database connectivity
- Class autoloading
- API routes
- Security configuration

### Step 2: Configure Environment
```bash
cp .env.example .env
# Edit .env with your settings
nano .env
```

Required configurations:
- `DB_TYPE` and database settings
- `API_KEY` for authentication
- `PFSENSE_HOST` and credentials
- AI provider keys
- CORS origins

### Step 3: Initialize Database
```bash
# The database is initialized automatically in bootstrap.php
# If you need to run migrations manually:
php -r "require 'vendor/autoload.php'; (new PfSenseAI\Database\Migration())->migrate();"
```

### Step 4: Set Up Scheduler
**Linux/macOS - Add to crontab:**
```bash
# Run every minute
* * * * * php /path/to/pfsense-ai-manager/scheduler-task.php >> /var/log/pfsense-scheduler.log 2>&1

# Or less frequently (every 5 minutes):
*/5 * * * * php /path/to/pfsense-ai-manager/scheduler-task.php >> /var/log/pfsense-scheduler.log 2>&1
```

**Windows - Task Scheduler:**
1. Open Task Scheduler
2. Create Basic Task
3. Trigger: Repeat every 5 minutes
4. Action: Start program
5. Program: `C:\php.exe`
6. Arguments: `C:\path\to\scheduler-task.php`

### Step 5: Start Web Server
```bash
# Development server
php -S localhost:8000 -t public/

# Production (use Apache/Nginx)
# Configure your web server to serve from `public/` directory
```

---

## 🧪 Testing Guide

### Test 1: Verify Installation
```bash
curl http://localhost:8000/api/system/status
```

### Test 2: List Agents
```bash
curl -H "Authorization: Bearer demo-token" \
  http://localhost:8000/api/agents
```

### Test 3: Create Schedule
```bash
curl -X POST http://localhost:8000/api/schedules \
  -H "Authorization: Bearer demo-token" \
  -H "Content-Type: application/json" \
  -d '{
    "agent_id": "recon_nmap",
    "frequency": "daily",
    "config": {"timeout": 300}
  }'
```

### Test 4: Execute Agent
```bash
curl -X POST http://localhost:8000/api/agents/recon_nmap/execute \
  -H "Authorization: Bearer demo-token" \
  -H "Content-Type: application/json" \
  -d '{"config": {}}'
```

### Test 5: Create Filter
```bash
curl -X POST http://localhost:8000/api/filters \
  -H "Authorization: Bearer demo-token" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Critical Severity",
    "type": "severity_level",
    "conditions": {"levels": ["critical"]}
  }'
```

### Test 6: List Schedules
```bash
curl -H "Authorization: Bearer demo-token" \
  http://localhost:8000/api/schedules
```

### Test 7: Execute Scheduled Jobs
```bash
curl -X POST http://localhost:8000/api/schedules/execute \
  -H "Authorization: Bearer demo-token"
```

---

## 📊 Dashboard Access

### Agent Dashboard
- **URL**: `http://localhost:8000/agents-dashboard.html`
- **Features**:
  - List all 50 agents with categories
  - Filter by agent category
  - Execute single or batch agents
  - View execution results
  - Real-time statistics

### Scheduler Dashboard
- **URL**: `http://localhost:8000/scheduler-dashboard.html`
- **Features**:
  - Create/manage schedules
  - View execution history
  - Manage filters
  - Configure scheduler settings
  - Statistics and monitoring

---

## 🔐 Security Checklist

- ✅ Bearer token authentication enabled
- ✅ Input validation for all endpoints
- ✅ CORS protection with whitelist
- ✅ Error message sanitization
- ✅ AES-256-GCM encryption for sensitive data
- ✅ Security headers (CSP, HSTS, X-Frame-Options)
- ✅ Environment-based configuration
- ✅ SQL injection prevention (prepared statements)
- ✅ Rate limiting (configurable)
- ✅ Audit logging for all operations

---

## 📝 Configuration Options

### Database
```env
DB_TYPE=sqlite                    # sqlite, mysql, pgsql
DB_PATH=storage/pfsense-ai.db     # SQLite database path
DB_HOST=localhost                 # MySQL/PostgreSQL host
DB_PORT=3306                      # MySQL/PostgreSQL port
DB_NAME=pfsense_ai               # Database name
DB_USER=root                      # Database user
DB_PASSWORD=                      # Database password
```

### Scheduler
```env
SCHEDULER_ENABLED=true            # Enable/disable scheduler
SCHEDULER_INTERVAL=5              # Check interval (minutes)
SCHEDULER_MAX_CONCURRENT=5        # Max parallel executions
SCHEDULER_RETENTION_DAYS=30       # Keep history (days)
```

### Filters
```env
FILTER_AUTO_APPLY=true            # Auto-apply default filters
FILTER_CACHE_TTL=3600             # Cache duration (seconds)
FILTER_DEFAULT_LIMIT=100          # Default result limit
FILTER_MAX_LIMIT=1000             # Maximum result limit
```

### Security
```env
API_KEY=your-secret-key           # API authentication key
ENCRYPTION_KEY=your-key           # Data encryption key
SECURE_CACHE_TTL=3600             # Secure cache TTL
PASSWORD_HASH_COST=12             # Bcrypt cost factor
```

---

## 🐛 Troubleshooting

### Issue: "Database connection failed"
**Solution**: 
1. Verify `DB_PATH` exists and is writable
2. Check `storage/` directory permissions
3. Ensure SQLite PHP extension is loaded

### Issue: "Authentication required"
**Solution**:
1. Include `Authorization: Bearer YOUR_TOKEN` header
2. Verify token in `API_KEY` or `AUTH_BEARER_TOKENS`
3. Check `CORS_ALLOWED_ORIGINS` configuration

### Issue: "Scheduler not executing"
**Solution**:
1. Verify cron job is running: `crontab -l`
2. Check cron logs: `tail -f /var/log/pfsense-scheduler.log`
3. Ensure `scheduler-task.php` has execute permissions: `chmod +x scheduler-task.php`
4. Verify database is accessible from cron context

### Issue: "Agent execution timeout"
**Solution**:
1. Increase `MAX_EXECUTION_TIME` in `.env`
2. Increase specific agent timeout (e.g., `RECON_TIMEOUT`)
3. Check agent configuration in `AgentOrchestrator.php`
4. Monitor server resources (CPU, memory)

---

## 📈 Monitoring & Maintenance

### View Logs
```bash
# Application logs
tail -f logs/app.log

# Scheduler logs
tail -f /var/log/pfsense-scheduler.log

# API request logs
tail -f logs/api.log
```

### Database Maintenance
```bash
# Backup database
cp storage/pfsense-ai.db storage/pfsense-ai.db.backup

# Clean old history (older than 30 days)
php -r "
  require 'vendor/autoload.php';
  \$db = PfSenseAI\Database\Database::getInstance();
  \$db->delete('execution_history', [
    'created_at' => ['<', date('Y-m-d', strtotime('-30 days'))]
  ]);
"
```

### Check Statistics
```bash
# Get agent statistics
curl -H "Authorization: Bearer demo-token" \
  http://localhost:8000/api/agents/stats

# Get scheduler statistics
curl -H "Authorization: Bearer demo-token" \
  http://localhost:8000/api/schedules/stats

# Get filter statistics
curl -H "Authorization: Bearer demo-token" \
  http://localhost:8000/api/filters/stats
```

---

## 🎯 Next Steps

1. **Configure Oblivion Integration** (Optional)
   - Set `OBLIVION_ENABLED=true` in `.env`
   - Configure Oblivion endpoint and credentials
   - Agents will integrate with Oblivion framework

2. **Enable Notifications** (Optional)
   - Configure email settings
   - Set Slack webhook for alerts
   - Configure notification triggers

3. **Set Up Monitoring** (Optional)
   - Configure centralized logging
   - Set up performance monitoring
   - Create dashboards

4. **Deploy to Production**
   - Use Apache/Nginx instead of development server
   - Configure SSL/TLS certificates
   - Set up database backups
   - Configure firewall rules

---

## 📞 Support & Documentation

**API Documentation**: See `API.md` in project root

**Architecture Guide**: See `pfsense-ai-manager/docs/` directory

**Configuration Examples**: See `.env.example` for all available options

**Dashboard Help**: Built-in help icons (?) in each dashboard

---

## ✨ Key Features Summary

| Feature | Status | Details |
|---------|--------|---------|
| 50 MITRE ATT&CK Agents | ✅ Active | 8 categories, customizable |
| Agent Scheduling | ✅ Active | Multiple frequencies, persistent |
| Advanced Filtering | ✅ Active | 8 filter types, composable |
| REST API | ✅ Active | 19 endpoints, standardized responses |
| Web Dashboards | ✅ Active | Real-time updates, responsive UI |
| Database Persistence | ✅ Active | SQLite/MySQL/PostgreSQL support |
| Security Hardening | ✅ Active | Authentication, encryption, validation |
| Cron Integration | ✅ Active | Automated job execution |
| Error Handling | ✅ Active | Comprehensive error responses |
| Audit Logging | ✅ Active | Complete execution tracking |

---

## 🎉 Deployment Complete!

Your Hackers6thSense is ready for deployment. All components are integrated, tested, and documented. Follow the installation steps above to get started.

**Happy securing! 🔒**
