# LEGION Integration - Complete Implementation

## ✅ Integration Complete

The pfSense AI Manager has been successfully integrated with the LEGION blue team defender framework. This creates a unified **red team/blue team threat orchestration and response system**.

## 🎯 What Was Added

### Core Components (3 PHP Classes)

#### 1. **LegionBridge.php** (300+ lines)
- **Purpose**: Gateway to LEGION defender API
- **Key Methods**:
  - `startDefenderSession()` - Initialize threat analysis
  - `analyzeThreat()` - Submit threats for analysis
  - `correlateAgentWithThreatIntel()` - Correlate agent results
  - `sendAlert()` - Trigger security notifications
  - `getAnalytics()` - Retrieve threat statistics

#### 2. **ThreatHandler.php** (400+ lines)
- **Purpose**: Automated threat escalation and response
- **Escalation Levels**:
  - Critical (L5): Immediate containment
  - High (L3): Investigation trigger
  - Medium (L2): Enhanced monitoring
  - Low (L1): Standard logging
- **Automated Actions**:
  - Block IP addresses
  - Quarantine resources
  - Isolate systems
  - Send alerts

#### 3. **LegionConfig.php** (80+ lines)
- **Purpose**: Configuration management
- **Manages**: 12 environment variables for LEGION integration

### API Endpoints (8 New)

```
1. POST   /api/legion/defender/start        - Start defender session
2. POST   /api/legion/analyze                - Analyze threat
3. POST   /api/legion/recommendations        - Get defense strategies
4. POST   /api/legion/correlate              - Correlate with threat intel
5. GET    /api/legion/threat-intel           - Retrieve threat database
6. GET    /api/legion/defender/status        - Check defender health
7. POST   /api/legion/alerts                 - Send security alert
8. GET    /api/legion/analytics              - Get threat analytics
```

### Database Extensions (2 New Tables)

#### legion_analysis
Stores threat analysis results from LEGION
```sql
- id, session_id, threat_data, analysis, threat_level, 
  recommendations, confidence, created_at
```

#### legion_correlations
Stores agent-to-threat correlations
```sql
- id, agent_id, execution_id, correlation, correlation_score, 
  threat_intel, created_at
```

### Dashboard
**unified-dashboard.html** (800+ lines)
- **Red Team Tab**: Agent orchestration and execution
- **Blue Team Tab**: Threat detection and defense
- **Correlation Tab**: Agent-to-threat correlations
- **Analytics Tab**: Charts and recommendations

### Updated Components

#### AgentOrchestrator.php
- Added LEGION imports and initialization
- Enhanced `executeAgent()` with automatic threat correlation
- After agent execution, triggers LEGION threat analysis
- Includes correlation status in API response

#### AgentScheduler.php
- Added LEGION threat handler initialization
- Enhanced `executeSchedule()` with threat analysis
- Starts LEGION defender session before scheduled execution
- Handles threat escalation based on threat level

### Documentation (4 Files)

1. **LEGION_INTEGRATION.md** - Complete technical guide
2. **LEGION_DEPLOYMENT_CHECKLIST.md** - Deployment procedures
3. **LEGION_DEPLOYMENT_STATUS.php** - Status monitoring
4. **LEGION_INTEGRATION_SUMMARY.md** - Implementation summary

## 📊 Statistics

| Item | Count | Details |
|------|-------|---------|
| PHP Classes Created | 3 | LegionBridge, ThreatHandler, LegionConfig |
| Lines of Code | 780+ | Across 3 new classes |
| API Endpoints | 8 | New LEGION-specific endpoints |
| Database Tables | 2 | legion_analysis, legion_correlations |
| HTML Dashboard | 1 | unified-dashboard.html (800+ lines) |
| Documentation | 4 | Guides, checklists, status monitoring |
| Total Lines Added | 3,395+ | All code, docs, and configuration |
| **Total Red+Blue Endpoints** | **27** | 19 red team + 8 LEGION |

## 🚀 Quick Start

### 1. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` and set:
```env
LEGION_ENABLED=true
LEGION_ENDPOINT=http://your-legion-server:3000/api
LEGION_API_KEY=your_api_key
LEGION_AUTO_CORRELATE=true
LEGION_INTEGRATION_MODE=passive
SECURITY_WEBHOOK_URL=https://your-webhook-url
SECURITY_ALERT_EMAIL=security@company.com
```

### 2. Initialize Database

```bash
php install.php
```

This runs migrations and creates all required tables including:
- ✓ legion_analysis
- ✓ legion_correlations

### 3. Test Integration

```bash
# Test LEGION endpoint connection
curl -X GET http://localhost/api/legion/defender/status

# Execute an agent with automatic LEGION correlation
curl -X POST http://localhost/api/agents/execute \
  -H "Content-Type: application/json" \
  -d '{"agent_id": "recon_001", "config": {}}'

# Check threat statistics
curl -X GET http://localhost/api/legion/analytics
```

### 4. Open Dashboard

```
http://your-server/unified-dashboard.html
```

View real-time red team and blue team operations side-by-side.

## 📋 Key Features

### Automated Threat Correlation
- Agent executes and produces results
- LEGION automatically analyzes results against threat intelligence
- Correlation score calculated and stored
- Alerts triggered if threat level exceeds threshold

### Multi-Level Threat Escalation
```
Critical (L5) → Immediate Response
   ├─ Block IP addresses
   ├─ Quarantine resources
   ├─ Isolate systems
   └─ Send immediate alerts

High (L3) → Investigation Triggered
   ├─ Create incident ticket
   ├─ Notify security team
   └─ Enhanced monitoring

Medium (L2) → Monitoring
   └─ Log for pattern analysis

Low (L1) → Standard Logging
   └─ Archive for audit trail
```

### Passive vs Active Mode

**Passive Mode** (Recommended for Initial Deployment):
```env
LEGION_INTEGRATION_MODE=passive
```
- Analyzes threats
- Generates recommendations
- Sends alerts
- Does NOT execute automated containment

**Active Mode** (After Validation):
```env
LEGION_INTEGRATION_MODE=active
```
- All passive mode features
- Automatically blocks IPs
- Quarantines resources
- Isolates affected systems
- Executes defense recommendations

### Real-Time Dashboard
- Red Team tab: 50 agents executing MITRE ATT&CK techniques
- Blue Team tab: Threats detected and threat levels
- Correlation tab: Agent-to-threat correlation heatmap
- Analytics tab: Trends and recommendations
- Auto-refresh every 30 seconds

## 🔧 Integration Workflow

```
┌──────────────────────────────────────────┐
│  Red Team Agent Executes                 │
│  (e.g., exploit_042 - T1190)             │
└──────────────┬───────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────┐
│  Results Stored in Database              │
│  (execution_history, agent_results)      │
└──────────────┬───────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────┐
│  LegionBridge Correlation                │
│  (if LEGION_AUTO_CORRELATE=true)         │
└──────────────┬───────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────┐
│  LEGION Threat Analysis                  │
│  (via LEGION_ENDPOINT API)               │
└──────────────┬───────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────┐
│  Threat Level Evaluation                 │
│  (0.0 - 1.0 confidence score)            │
└──────────────┬───────────────────────────┘
               │
               ▼
┌──────────────────────────────────────────┐
│  Store Correlation Result                │
│  (legion_correlations table)             │
└──────────────┬───────────────────────────┘
               │
               ▼
        ┌──────────────┐
        │ Is Threat    │
        │ Level High?  │
        └──┬───────┬───┘
           │ YES   │ NO
           ▼       ▼
        ┌───┐  Continue
        │   │
        ▼   
┌──────────────────────────────────────────┐
│  ThreatHandler Escalation                │
│  - Evaluate confidence score             │
│  - Determine escalation level            │
│  - Execute appropriate actions           │
└──────────────┬───────────────────────────┘
               │
         ┌─────┼─────┬─────────────┐
         │     │     │             │
         ▼     ▼     ▼             ▼
       CRIT  HIGH  MED            LOW
         │     │     │             │
         ▼     ▼     ▼             ▼
       Block  Alert Monitor      Log
```

## 📚 Documentation

### Quick Reference
- **Get Started**: README.md (this file)
- **Full Guide**: LEGION_INTEGRATION.md
- **Deployment**: LEGION_DEPLOYMENT_CHECKLIST.md
- **Monitoring**: LEGION_DEPLOYMENT_STATUS.php
- **Summary**: LEGION_INTEGRATION_SUMMARY.md

### View Documentation

```bash
# Full integration guide
cat LEGION_INTEGRATION.md

# Deployment procedures
cat LEGION_DEPLOYMENT_CHECKLIST.md

# Check deployment status (JSON)
curl http://localhost/LEGION_DEPLOYMENT_STATUS.php

# Or check via CLI
php LEGION_DEPLOYMENT_STATUS.php
```

## 🧪 Test Scenarios

### Scenario 1: Execute Agent with Auto-Correlation

```bash
curl -X POST http://localhost/api/agents/execute \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "agent_id": "exploit_042",
    "config": {}
  }'

# Expected: Response includes "legion_correlated": true
```

### Scenario 2: Submit Critical Threat

```bash
curl -X POST http://localhost/api/legion/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "threat_type": "ransomware",
    "threat_level": 5,
    "confidence": 0.98,
    "indicator": "malicious_payload"
  }'

# Expected:
# - Alert sent to SECURITY_WEBHOOK_URL
# - Email sent to SECURITY_ALERT_EMAIL
# - Containment actions logged (if INTEGRATION_MODE=active)
```

### Scenario 3: View Threat Analytics

```bash
curl -X GET http://localhost/api/legion/analytics

# Expected Response:
{
  "critical": 5,
  "high": 12,
  "medium": 28,
  "low": 45,
  "avg_confidence": 0.78,
  "total_threats": 90
}
```

## ⚙️ Configuration Reference

### Essential Variables

```env
# LEGION Enablement
LEGION_ENABLED=true

# LEGION Server Connection
LEGION_ENDPOINT=http://localhost:3000/api
LEGION_API_KEY=your_secure_api_key

# AI Providers (for LEGION threat analysis)
LEGION_PROVIDERS=groq,gemini,mistral

# Threat Correlation
LEGION_AUTO_CORRELATE=true
LEGION_CORRELATION_THRESHOLD=0.7

# Alert Triggers
LEGION_ALERT_ON_THREAT=true
LEGION_THREAT_THRESHOLD=3

# Operation Mode
LEGION_INTEGRATION_MODE=passive  # Start with passive

# Alert Channels
SECURITY_WEBHOOK_URL=https://your-webhook-url.com
SECURITY_ALERT_EMAIL=security@company.com
```

### Advanced Options

```env
# Threat Analysis Defaults
LEGION_DEFAULT_THREAT_LEVEL=3

# Caching
LEGION_CACHE_TTL=3600

# Logging
LOG_LEVEL=INFO
LOG_PATH=/var/log/pfsense-ai-manager/

# Database
DB_TYPE=mysql
DB_HOST=localhost
DB_USER=pfense_ai
DB_PASSWORD=secure_password
DB_NAME=pfense_ai
```

## 🛡️ Security Considerations

### Authentication
- ✓ All LEGION API calls use `LEGION_API_KEY`
- ✓ API endpoints require bearer token
- ✓ Database connections use encrypted credentials

### Data Protection
- ✓ Threat data encrypted in transit (HTTPS/TLS)
- ✓ Credentials stored in `.env` (not in code)
- ✓ Sensitive logs sanitized before rotation
- ✓ Audit trail maintained for compliance

### Operational Safety
- ✓ Start in passive mode (analysis only)
- ✓ Validate 48-72 hours before active mode
- ✓ All automated actions logged for audit
- ✓ Rollback procedures documented

## 📈 Performance

Expected metrics after deployment:

| Metric | Target | Max |
|--------|--------|-----|
| Agent Execution | 5-30s | 60s |
| Threat Correlation | <100ms | 500ms |
| Threat Analysis | 500-2000ms | 5000ms |
| Dashboard Load | <2s | 5s |
| API Response | <200ms | 500ms |

## 🔍 Monitoring

### Check System Status

```bash
# JSON status report
curl http://localhost/LEGION_DEPLOYMENT_STATUS.php

# CLI status report
php LEGION_DEPLOYMENT_STATUS.php
```

### View Logs

```bash
# Application logs
tail -f /var/log/pfsense-ai-manager/app.log

# Error logs
tail -f /var/log/pfsense-ai-manager/error.log

# Search for LEGION activity
grep "LEGION\|threat" /var/log/pfsense-ai-manager/*.log
```

### Database Queries

```bash
# Recent executions
mysql -u root -p pfense_ai -e \
  "SELECT * FROM execution_history ORDER BY id DESC LIMIT 10;"

# Recent threats
mysql -u root -p pfense_ai -e \
  "SELECT * FROM legion_analysis ORDER BY id DESC LIMIT 10;"

# Correlations
mysql -u root -p pfense_ai -e \
  "SELECT agent_id, correlation_score FROM legion_correlations \
   ORDER BY correlation_score DESC LIMIT 10;"
```

## 🚨 Troubleshooting

### LEGION Connection Failed

```
Error: Cannot reach LEGION endpoint
Solution:
1. Verify LEGION_ENDPOINT in .env
2. Check LEGION server is running
3. Test connectivity: curl http://legion-server:3000/api/health
4. Verify firewall rules
```

### Threats Not Correlating

```
Error: No entries in legion_correlations
Solution:
1. Verify LEGION_ENABLED=true
2. Verify LEGION_AUTO_CORRELATE=true
3. Execute agent: php -r "..."
4. Check logs for errors
```

### High False Positive Rate

```
Error: Too many low-confidence threats
Solution:
1. Increase LEGION_CORRELATION_THRESHOLD (0.7 → 0.8)
2. Increase LEGION_THREAT_THRESHOLD (3 → 4)
3. Review agent result quality
4. Calibrate LEGION AI providers
```

## 📞 Support

- **Integration Guide**: `LEGION_INTEGRATION.md`
- **Deployment Help**: `LEGION_DEPLOYMENT_CHECKLIST.md`
- **Status Monitoring**: `LEGION_DEPLOYMENT_STATUS.php`
- **Dashboard**: `http://your-server/unified-dashboard.html`

## 🎓 Learning Resources

1. **Start Here**: README.md (this file)
2. **Deep Dive**: LEGION_INTEGRATION.md
3. **Go Live**: LEGION_DEPLOYMENT_CHECKLIST.md
4. **Monitor**: Dashboard + LEGION_DEPLOYMENT_STATUS.php

## ✨ What's Next

### Immediate (Day 1)
- [ ] Configure `.env` with LEGION credentials
- [ ] Run `php install.php`
- [ ] Test agent execution
- [ ] Verify dashboard loads

### Short Term (Week 1)
- [ ] Deploy in passive mode
- [ ] Monitor for 48-72 hours
- [ ] Review correlation accuracy
- [ ] Train security team

### Medium Term (Week 2-3)
- [ ] Transition to active mode
- [ ] Begin automated containment
- [ ] Establish incident response procedures
- [ ] Fine-tune threat thresholds

### Long Term
- [ ] Advanced threat hunting workflows
- [ ] Machine learning correlation improvements
- [ ] Multi-LEGION instance support
- [ ] Custom defense playbooks

## 📝 Summary

**LEGION integration is complete and production-ready!**

### What You Now Have
✅ 50 autonomous red team agents (MITRE ATT&CK coverage)
✅ Real-time threat correlation engine
✅ Automated multi-level threat escalation
✅ Unified red/blue team dashboard
✅ 27 production-ready API endpoints
✅ Comprehensive documentation

### Next Step
1. Configure `.env` with LEGION connection details
2. Run `php install.php` to initialize database
3. Test integration with provided test scenarios
4. Deploy in passive mode for 48-72 hour validation
5. Transition to active mode upon validation completion

---

**Ready to integrate?** Start with `LEGION_INTEGRATION.md` for detailed technical guidance!
