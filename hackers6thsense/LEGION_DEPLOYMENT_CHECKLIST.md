# LEGION Integration Deployment Checklist

## Pre-Deployment Verification

### 1. File Structure ✓
All required LEGION integration files are in place:

```
✓ src/Integration/LEGION/
  ✓ LegionBridge.php              - LEGION API gateway (300+ lines)
  ✓ LegionConfig.php              - Configuration manager (80+ lines)
  ✓ ThreatHandler.php             - Threat escalation engine (400+ lines)

✓ src/API/Endpoints/
  ✓ LegionEndpoint.php            - 8 API endpoints (250+ lines)

✓ src/Agents/
  ✓ AgentOrchestrator.php         - UPDATED with LEGION correlation
  ✓ AgentScheduler.php            - UPDATED with threat handling

✓ public/
  ✓ unified-dashboard.html        - Red/Blue team dashboard (800+ lines)

✓ Documentation/
  ✓ LEGION_INTEGRATION.md         - Complete integration guide
  ✓ LEGION_DEPLOYMENT_CHECKLIST.md - This file
```

### 2. Database Schema ✓
Required tables are created:

```sql
-- Base tables (pre-existing)
✓ agents                  - Agent definitions
✓ schedules              - Scheduled executions
✓ execution_history      - Execution records
✓ agent_results          - Agent result storage
✓ filters                - Data filters

-- LEGION tables (NEW)
✓ legion_analysis        - Threat analysis results
✓ legion_correlations    - Agent-threat correlations
```

**Verification**:
```bash
php install.php  # Runs migrations automatically
```

### 3. Configuration ✓
All required environment variables are defined:

```env
# LEGION Configuration
✓ LEGION_ENABLED=true
✓ LEGION_ENDPOINT=http://localhost:3000/api
✓ LEGION_API_KEY=your_key
✓ LEGION_PROVIDERS=groq,gemini,mistral
✓ LEGION_AUTO_CORRELATE=true
✓ LEGION_INTEGRATION_MODE=passive (recommended for initial deployment)

# Alert Configuration
✓ SECURITY_WEBHOOK_URL=https://your-webhook
✓ SECURITY_ALERT_EMAIL=security@company.com
```

**Verification**:
```bash
cp .env.example .env
# Edit .env with production values
php -r "echo getenv('LEGION_ENABLED');"  # Should output: true
```

### 4. API Routes ✓
All 8 LEGION endpoints are registered:

```
✓ POST   /api/legion/defender/start          - Start defender session
✓ POST   /api/legion/analyze                 - Analyze threat
✓ POST   /api/legion/recommendations         - Get recommendations
✓ POST   /api/legion/correlate               - Correlate with threat intel
✓ GET    /api/legion/threat-intel            - Fetch threat intelligence
✓ GET    /api/legion/defender/status         - Check defender status
✓ POST   /api/legion/alerts                  - Send alert
✓ GET    /api/legion/analytics               - Get threat analytics
```

**Verification**:
```bash
curl -X GET http://localhost/api/legion/defender/status \
  -H "Authorization: Bearer YOUR_API_KEY"
```

## Pre-Production Testing

### Step 1: Test LEGION Connection

```bash
# Test LEGION endpoint connectivity
curl -X GET http://localhost:3000/api/health

# Expected Response:
# {"status": "online", "version": "1.0.0"}

# If failed:
# - Verify LEGION server is running
# - Check LEGION_ENDPOINT in .env
# - Verify firewall rules
# - Check LEGION_API_KEY
```

### Step 2: Test Agent Execution with Correlation

```bash
# Execute a single agent
curl -X POST http://localhost/api/agents/execute \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "agent_id": "recon_001",
    "config": {}
  }'

# Expected Response:
# {
#   "status": "success",
#   "execution_id": 1,
#   "result": {...},
#   "legion_correlated": true
# }

# Verify correlation was stored
mysql -u root -p pfense_ai -e \
  "SELECT * FROM legion_correlations WHERE execution_id = 1;"
```

### Step 3: Test Threat Analysis

```bash
# Submit threat for analysis
curl -X POST http://localhost/api/legion/analyze \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "threat_type": "recon",
    "threat_level": 2,
    "indicator": "192.168.1.100",
    "source": "agent_001"
  }'

# Expected Response:
# {
#   "session_id": "threat_abc123",
#   "analysis": "...",
#   "recommendations": [...]
# }
```

### Step 4: Test Threat Escalation

```bash
# Test critical threat response
curl -X POST http://localhost/api/legion/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "threat_type": "exploitation",
    "threat_level": 5,
    "confidence": 0.95,
    "indicator": "malicious_payload"
  }'

# Expected:
# - Alert sent to SECURITY_WEBHOOK_URL
# - Email sent to SECURITY_ALERT_EMAIL
# - Entry created in legion_analysis with threat_level=5
# - Entry created in legion_correlations (if from agent)
# - Logs contain "CRITICAL THREAT" entry

# Verify alert was sent
grep "CRITICAL THREAT" /var/log/pfsense-ai-manager/error.log
```

### Step 5: Test Scheduled Execution with LEGION

```bash
# Create schedule
curl -X POST http://localhost/api/schedules/create \
  -H "Content-Type: application/json" \
  -d '{
    "agent_id": "monitor_001",
    "frequency": "hourly",
    "config": {}
  }'

# Run scheduler
php scheduler-task.php

# Expected:
# - Agent executes
# - Results correlated with LEGION
# - Threats analyzed if level exceeds threshold
# - Logs show successful correlation
```

### Step 6: Dashboard Functionality Test

```bash
# Open unified dashboard
http://localhost/unified-dashboard.html

# Test functionality:
✓ Red Team tab loads agent data
✓ Blue Team tab loads threat data
✓ Correlation tab shows correlation heatmap
✓ Analytics tab displays charts
✓ Refresh buttons work correctly
✓ Agent execute buttons function
✓ Real-time updates every 30 seconds
```

## Production Deployment

### Phase 1: Passive Mode Deployment

1. **Set Integration Mode to Passive**
   ```env
   LEGION_INTEGRATION_MODE=passive
   ```
   This mode analyzes threats but does NOT execute automated responses.

2. **Deploy Files**
   ```bash
   git pull origin main
   php composer.json install
   php install.php  # Run migrations
   ```

3. **Verify Deployment**
   ```bash
   curl -X GET http://your-server/DEPLOYMENT_STATUS.php
   ```

4. **Monitor Logs**
   ```bash
   tail -f /var/log/pfsense-ai-manager/error.log
   tail -f /var/log/pfsense-ai-manager/app.log
   ```

5. **Observe for 48 hours**
   - Monitor correlation accuracy
   - Check false positive rate
   - Verify alert delivery
   - Review threat patterns

### Phase 2: Active Mode Transition

**Conditions for transition**:
- ✓ False positive rate < 5%
- ✓ All 50 agents executing successfully
- ✓ Threat correlations > 90% accurate
- ✓ Alert delivery 100% successful
- ✓ No API errors or timeouts
- ✓ Database performance acceptable

**Transition Steps**:

1. **Update Configuration**
   ```env
   LEGION_INTEGRATION_MODE=active
   LEGION_AUTO_CORRELATE=true
   LEGION_ALERT_ON_THREAT=true
   ```

2. **Test Containment in Lab**
   ```bash
   # Execute controlled threat scenario
   curl -X POST http://test-server/api/legion/analyze \
     -d '{
       "threat_type": "malware",
       "threat_level": 4,
       "confidence": 0.95
     }'
   
   # Verify containment actions logged
   # Verify no unintended side effects
   ```

3. **Deploy Active Mode**
   - Update production .env
   - Notify security team
   - Establish incident response procedures

4. **Monitor Active Mode (72 hours)**
   - Review all containment actions
   - Verify no false positive responses
   - Check service availability
   - Monitor performance

## Post-Deployment Verification

### 1. System Health Check

```bash
# Run status script
php DEPLOYMENT_STATUS.php

# Expected Output:
# ✓ Database Connection: OK
# ✓ LEGION Bridge: Connected
# ✓ API Endpoints: 27/27 registered
# ✓ Recent Executions: N agents executed
# ✓ Threat Statistics: M threats analyzed
# ✓ System Status: Operational
```

### 2. Data Verification

```bash
# Verify agents are executing
mysql -u root -p pfense_ai -e \
  "SELECT COUNT(*) as total_executions FROM execution_history WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);"

# Expected: > 0 executions in last 24 hours

# Verify correlations are created
mysql -u root -p pfense_ai -e \
  "SELECT COUNT(*) as total_correlations FROM legion_correlations WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR);"

# Expected: > 0 correlations in last 24 hours

# Verify threat analysis is working
mysql -u root -p pfense_ai -e \
  "SELECT COUNT(*) as threat_count, AVG(threat_level) as avg_level, AVG(confidence) as avg_conf FROM legion_analysis;"

# Expected: > 0 threats analyzed, reasonable threat levels and confidence
```

### 3. API Endpoint Verification

Test all 8 LEGION endpoints:

```bash
#!/bin/bash

API="http://localhost"
TOKEN="your_api_token"
HEADERS="-H 'Content-Type: application/json' -H 'Authorization: Bearer $TOKEN'"

echo "Testing LEGION API Endpoints..."

# 1. Start Defender
curl -X POST $API/api/legion/defender/start $HEADERS \
  -d '{"agent_id": "test_001"}' && echo "✓ Defender Start" || echo "✗ Defender Start"

# 2. Analyze Threat
curl -X POST $API/api/legion/analyze $HEADERS \
  -d '{"threat_type": "test", "threat_level": 2}' && echo "✓ Analyze" || echo "✗ Analyze"

# 3. Get Recommendations
curl -X POST $API/api/legion/recommendations $HEADERS \
  -d '{"threat_id": "test_123"}' && echo "✓ Recommendations" || echo "✗ Recommendations"

# 4. Correlate
curl -X POST $API/api/legion/correlate $HEADERS \
  -d '{"agent_id": 1, "execution_id": 1}' && echo "✓ Correlate" || echo "✗ Correlate"

# 5. Threat Intel
curl -X GET "$API/api/legion/threat-intel?type=test" $HEADERS && echo "✓ Threat Intel" || echo "✗ Threat Intel"

# 6. Defender Status
curl -X GET $API/api/legion/defender/status $HEADERS && echo "✓ Status" || echo "✗ Status"

# 7. Send Alert
curl -X POST $API/api/legion/alerts $HEADERS \
  -d '{"level": "info", "message": "Test"}' && echo "✓ Alert" || echo "✗ Alert"

# 8. Analytics
curl -X GET $API/api/legion/analytics $HEADERS && echo "✓ Analytics" || echo "✗ Analytics"
```

### 4. Dashboard Verification

- [ ] Unified dashboard loads without errors
- [ ] Red Team tab displays agents
- [ ] Blue Team tab displays threats
- [ ] Correlation heatmap shows data
- [ ] Analytics charts render correctly
- [ ] All buttons are functional
- [ ] Auto-refresh works every 30 seconds
- [ ] Mobile responsive layout works

### 5. Alert Delivery Test

```bash
# Trigger test alert
curl -X POST http://localhost/api/legion/alerts \
  -H "Content-Type: application/json" \
  -d '{
    "level": "critical",
    "type": "test_alert",
    "message": "Test alert from deployment verification"
  }'

# Verify webhook received alert
# Verify email was sent to SECURITY_ALERT_EMAIL
# Check logs for alert delivery confirmation
```

## Rollback Procedure

If issues occur during deployment:

### Rollback to Previous Version

```bash
# 1. Stop active processes
sudo systemctl stop pfsense-ai-manager

# 2. Rollback code
git checkout previous_tag
composer install

# 3. Disable LEGION integration
sed -i 's/LEGION_ENABLED=true/LEGION_ENABLED=false/' .env

# 4. Restart services
sudo systemctl start pfsense-ai-manager

# 5. Verify
curl http://localhost/DEPLOYMENT_STATUS.php
```

## Common Issues and Solutions

### Issue: LEGION Endpoint Unreachable

**Symptoms**: All LEGION calls timeout
**Solution**:
1. Verify LEGION server is running
2. Check firewall rules for port 3000
3. Verify LEGION_ENDPOINT in .env
4. Check network connectivity: `ping legion_server`
5. Set `LEGION_ENABLED=false` temporarily until resolved

### Issue: High Database Load

**Symptoms**: Slow correlation creation, timeouts
**Solution**:
1. Add database indexes (see LEGION_INTEGRATION.md)
2. Increase `LEGION_CACHE_TTL` to reduce queries
3. Archive old correlation data
4. Consider database optimization: `OPTIMIZE TABLE legion_analysis`

### Issue: Too Many False Positive Alerts

**Symptoms**: Excessive alerts, poor correlation accuracy
**Solution**:
1. Increase `LEGION_THREAT_THRESHOLD` (3 → 4)
2. Increase `LEGION_CORRELATION_THRESHOLD` (0.7 → 0.8)
3. Review LEGION AI provider calibration
4. Disable auto-correlation temporarily to debug

### Issue: Containment Actions Failing

**Symptoms**: Threats detected but no response executed
**Solution**:
1. Verify `LEGION_INTEGRATION_MODE=active`
2. Check pfSense XML-RPC API access
3. Verify firewall permissions
4. Test containment actions manually
5. Review containment logs for errors

## Performance Metrics Baseline

After successful deployment, expected metrics:

| Metric | Target | Acceptable Range |
|--------|--------|------------------|
| Agent Execution Time | 5-30s | 2-60s |
| Correlation Creation | < 100ms | < 500ms |
| Threat Analysis Time | 500-2000ms | 200-5000ms |
| Dashboard Load Time | < 2s | < 5s |
| API Response Time | < 200ms | < 500ms |
| Database Query Time | < 100ms | < 300ms |
| System CPU Usage | 10-20% | < 40% |
| Memory Usage | 256-512MB | < 1GB |

## Compliance and Security

### Post-Deployment Security Review

- [ ] All API endpoints require authentication
- [ ] LEGION_API_KEY is securely stored
- [ ] Threat data is encrypted in transit (HTTPS)
- [ ] Database credentials are not in source code
- [ ] Logs do not contain sensitive information
- [ ] Alert webhooks use HTTPS
- [ ] Email alerts are sent securely
- [ ] Audit trails are comprehensive
- [ ] Backup strategy is in place
- [ ] Incident response procedures documented

## Support Contacts

- **LEGION Support**: [LEGION documentation link]
- **pfSense AI Manager Issues**: [Support ticket system]
- **Security Incidents**: [Security contact email]
- **On-Call**: [Emergency contact phone]

## Sign-Off

### Deployment Verification Sign-Off

**Date**: ___________
**Deployed By**: ___________
**Verified By**: ___________
**Approver**: ___________

### Comments
```
_____________________________________________________________________________
_____________________________________________________________________________
_____________________________________________________________________________
```

---

**Deployment Complete**: All LEGION integration components deployed successfully!

**Next Steps**:
1. Monitor system for 48 hours in passive mode
2. Review correlation accuracy metrics
3. Transition to active mode upon approval
4. Establish ongoing monitoring and maintenance procedures
5. Document any customizations or deviations
