# LEGION Integration Guide

## Overview

The pfSense AI Manager now features complete integration with the LEGION blue team defender framework, creating a unified red team/blue team coordination system. This enables:

- **Automated threat correlation**: Agent results automatically analyzed by LEGION threat intelligence
- **Real-time threat escalation**: Multi-level threat handling from info to critical
- **Automated containment**: Active response mechanisms for critical threats
- **Comprehensive dashboards**: Unified visualization of both red and blue team operations
- **Historical analysis**: Complete audit trail of agent executions and threat correlations

## Architecture

### Integration Components

```
┌─────────────────────────────────────────────────────────────┐
│         pfSense AI Manager (Red Team)                       │
│  ┌──────────────────┐         ┌──────────────────────────┐ │
│  │ 50 Agents        │         │ Agent Orchestrator       │ │
│  │ - Recon          │───────▶ │ - Executes agents        │ │
│  │ - Exploit        │         │ - Stores results         │ │
│  │ - Post-Exploit   │         │ - Triggers LEGION        │ │
│  └──────────────────┘         └──────────────────────────┘ │
└────────────┬────────────────────────────────────────────────┘
             │
             │ HTTP/WebSocket
             │ (LegionBridge)
             ▼
┌─────────────────────────────────────────────────────────────┐
│         LEGION Blue Team Defender                           │
│  ┌──────────────────┐         ┌──────────────────────────┐ │
│  │ Threat Analysis  │         │ AI-Powered Defense       │ │
│  │ - Groq           │───────▶ │ - Threat correlation     │ │
│  │ - Gemini         │         │ - Recommendations        │ │
│  │ - Mistral        │         │ - Automated response     │ │
│  └──────────────────┘         └──────────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

### Key Classes

#### 1. **LegionBridge** (`src/Integration/LEGION/LegionBridge.php`)
Gateway to LEGION defender framework.

**Key Methods:**
- `startDefenderSession($config)` - Initialize LEGION session
- `analyzeThreat($threatData)` - Send threat for analysis
- `getDefenseRecommendations($threatId)` - Retrieve defense strategies
- `correlateAgentWithThreatIntel($agentId, $executionId, $result)` - Correlate agent results with threat intelligence
- `fetchThreatIntelligence()` - Retrieve threat database
- `getDefenderStatus()` - Check LEGION health
- `sendAlert($alert)` - Trigger security alerts
- `getAnalytics()` - Retrieve threat statistics

#### 2. **ThreatHandler** (`src/Integration/LEGION/ThreatHandler.php`)
Manages threat escalation and automated responses.

**Key Methods:**
- `handleThreat($threatData, $executionId, $agentId)` - Route threat to appropriate handler
- `handleCriticalThreat(...)` - Immediate response execution
- `handleHighThreat(...)` - Prompt investigation trigger
- `handleMediumThreat(...)` - Enhanced monitoring
- `handleLowThreat(...)` - Standard logging
- `executeContainment(...)` - Run automated containment procedures
- `getThreatHistory($agentId)` - Retrieve threat correlation history
- `getThreatStatistics()` - Get threat metrics

#### 3. **LegionConfig** (`src/Integration/LEGION/LegionConfig.php`)
Configuration management for LEGION integration.

**Methods:**
- `isEnabled()` - Check if LEGION integration is active
- `getEndpoint()` - LEGION server URL
- `getApiKey()` - LEGION API authentication
- `getProviders()` - AI providers (groq, gemini, mistral)
- `shouldAutoCorrelate()` - Auto-correlation setting
- `getCorrelationThreshold()` - Minimum correlation score
- `getThreatThreshold()` - Threat level for alerts
- `getIntegrationMode()` - passive/active mode

## Configuration

### Environment Variables

Add these to `.env`:

```env
# LEGION Integration
LEGION_ENABLED=true
LEGION_ENDPOINT=http://localhost:3000/api
LEGION_API_KEY=your_legion_api_key_here
LEGION_PROVIDERS=groq,gemini,mistral
LEGION_DEFAULT_THREAT_LEVEL=3
LEGION_AUTO_CORRELATE=true
LEGION_CORRELATION_THRESHOLD=0.7
LEGION_ALERT_ON_THREAT=true
LEGION_THREAT_THRESHOLD=3
LEGION_INTEGRATION_MODE=active
LEGION_CACHE_TTL=3600

# Alert Settings
SECURITY_WEBHOOK_URL=https://your-webhook-url.com
SECURITY_ALERT_EMAIL=security@yourcompany.com
```

### Configuration Levels

**Passive Mode**: LEGION analyzes results but does not execute containment
```env
LEGION_INTEGRATION_MODE=passive
```

**Active Mode**: LEGION executes automated containment procedures
```env
LEGION_INTEGRATION_MODE=active
```

## Usage

### 1. Automatic Agent Correlation

When agents execute, LEGION automatically analyzes results:

```php
// In AgentOrchestrator.php executeAgent()
if ($this->legionConfig->isEnabled() && $this->legionConfig->shouldAutoCorrelate()) {
    $this->legionBridge->correlateAgentWithThreatIntel($agentId, $executionId, $result);
}
```

### 2. Scheduled Threat Analysis

Scheduled agents trigger LEGION analysis during execution:

```php
// In AgentScheduler.php executeSchedule()
$threatAnalysis = $this->threatHandler->handleThreat(
    ['type' => $threatType, 'threat_level' => $level, ...],
    $executionId,
    $agentId
);
```

### 3. API Endpoints

#### Start LEGION Session
```
POST /api/legion/defender/start
Content-Type: application/json

{
  "agent_id": "recon_001",
  "schedule_id": 1,
  "execution_type": "scheduled"
}
```

Response:
```json
{
  "session_id": "sess_abc123",
  "status": "active",
  "defender_ready": true
}
```

#### Analyze Threat
```
POST /api/legion/analyze
Content-Type: application/json

{
  "threat_type": "malware",
  "threat_level": 4,
  "indicator": "192.168.1.100",
  "source": "agent_123"
}
```

#### Get Defense Recommendations
```
POST /api/legion/recommendations
Content-Type: application/json

{
  "threat_id": "threat_xyz",
  "execution_id": 42
}
```

Response:
```json
{
  "recommendations": [
    {"action": "block_ip", "target": "192.168.1.100", "priority": "critical"},
    {"action": "quarantine", "target": "process_pid", "priority": "high"}
  ],
  "confidence": 0.92
}
```

#### Correlate with Threat Intelligence
```
POST /api/legion/correlate
Content-Type: application/json

{
  "agent_id": "exploit_042",
  "execution_id": 99,
  "result": {...}
}
```

#### Get Threat Intelligence
```
GET /api/legion/threat-intel?type=malware&days=7
```

#### Get Defender Status
```
GET /api/legion/defender/status
```

#### Send Alert
```
POST /api/legion/alerts
Content-Type: application/json

{
  "level": "critical",
  "type": "ransomware",
  "message": "Ransomware detected by agent",
  "data": {...}
}
```

#### Get Analytics
```
GET /api/legion/analytics
```

Response:
```json
{
  "critical": 5,
  "high": 12,
  "medium": 28,
  "low": 45,
  "avg_confidence": 0.78,
  "total_threats": 90
}
```

## Threat Escalation Levels

### Critical (Level 4-5)
- **Confidence**: ≥ 80%
- **Action**: Immediate response
- **Response**:
  - Alert security team immediately
  - Webhook notification
  - Email alert
  - Execute automated containment (if active mode)
  - Block IP addresses
  - Isolate affected systems
  - Quarantine resources

### High (Level 3)
- **Confidence**: ≥ 70%
- **Action**: Prompt investigation
- **Response**:
  - Create high-priority ticket
  - Notify security analysts
  - Suggest containment actions
  - Enhanced monitoring enabled

### Medium (Level 2)
- **Confidence**: ≥ 60%
- **Action**: Enhanced monitoring
- **Response**:
  - Log for audit trail
  - Monitor for pattern escalation
  - Weekly analyst review

### Low (Level 1)
- **Confidence**: ≥ 50%
- **Action**: Standard logging
- **Response**:
  - Archive for historical analysis
  - Monthly trend review

### Informational
- **Confidence**: < 50%
- **Action**: Logged only
- **Response**:
  - Debug logging
  - No alert trigger

## Database Schema Extensions

### legion_analysis Table
Stores threat analysis from LEGION defender.

```sql
CREATE TABLE legion_analysis (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    session_id VARCHAR(255) UNIQUE,
    threat_data JSON,
    analysis TEXT,
    threat_level INT (1-5),
    recommendations JSON,
    confidence DECIMAL(3,2) (0-1),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### legion_correlations Table
Stores correlations between agent results and threat intelligence.

```sql
CREATE TABLE legion_correlations (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    agent_id INT,
    execution_id INT,
    correlation JSON,
    correlation_score DECIMAL(3,2),
    threat_intel JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (execution_id) REFERENCES execution_history(id)
);
```

## Workflow Examples

### Example 1: Automated Exploit Detection and Response

1. **Exploit Agent Executes**
   ```
   Agent: exploit_042 (MITRE: T1190 - Exploit Public-Facing Application)
   Result: Successful exploitation detected at 192.168.1.50:8080
   ```

2. **LEGION Threat Analysis**
   ```
   Threat Type: Successful Remote Code Execution
   Threat Level: 5 (Critical)
   Confidence: 0.95 (95%)
   ```

3. **Threat Escalation**
   ```
   Level: CRITICAL → Immediate Response
   ```

4. **Automated Actions**
   ```
   ✓ Block source IP 192.168.1.50
   ✓ Isolate affected host
   ✓ Quarantine process
   ✓ Send alert to security team
   ✓ Create incident ticket
   ✓ Archive evidence
   ```

### Example 2: Anomaly Detection with Monitoring

1. **Recon Agent Discovers Anomaly**
   ```
   Agent: network_monitor_001
   Finding: Unusual outbound traffic pattern detected
   ```

2. **LEGION Analysis**
   ```
   Threat Type: Potential Data Exfiltration
   Threat Level: 3 (High)
   Confidence: 0.75 (75%)
   ```

3. **Investigation Triggered**
   ```
   Priority: High
   Action: Security analyst review
   Monitoring: Enhanced traffic analysis enabled
   ```

4. **Follow-up Actions**
   ```
   ✓ Correlation with threat intelligence
   ✓ Historical pattern analysis
   ✓ Recommended: Increase DLP sensitivity
   ✓ Recommended: Deploy honeypot
   ```

## Dashboard Features

### Unified Dashboard (`public/unified-dashboard.html`)

**Red Team Tab:**
- Agent orchestration status
- Real-time execution tracking
- Agent activity log
- Performance metrics

**Blue Team Tab:**
- Threat statistics (critical/high/medium/low)
- Recent threat detections
- Average confidence scores
- Defense status

**Correlation Tab:**
- Agent-to-threat correlation heatmap
- Correlation scores
- Top correlations
- Trend analysis

**Analytics Tab:**
- Threat trend charts
- Agent execution statistics
- System recommendations
- Performance dashboards

## Integration Testing

### Test Scenario 1: Critical Threat Response

```bash
# Trigger critical threat scenario
curl -X POST http://localhost/api/legion/analyze \
  -H "Content-Type: application/json" \
  -d '{
    "threat_type": "ransomware",
    "threat_level": 5,
    "indicator": "malware.exe",
    "confidence": 0.98
  }'

# Expected: Immediate containment, alerts sent
```

### Test Scenario 2: Correlation Accuracy

```bash
# Execute agent and verify correlation
curl -X POST http://localhost/api/agents/execute \
  -H "Content-Type: application/json" \
  -d '{"agent_id": "exploit_042", "config": {}}'

# Verify: Check legion_correlations table for entry
mysql -u root -p pfense_ai -e "SELECT * FROM legion_correlations ORDER BY id DESC LIMIT 1;"
```

### Test Scenario 3: Escalation Levels

```bash
# Verify each escalation level is triggered correctly
# Critical (L5), High (L3), Medium (L2), Low (L1), Info (<L1)

for level in 5 3 2 1; do
  curl -X POST http://localhost/api/legion/analyze \
    -d "{\"threat_level\": $level, ...}"
done
```

## Troubleshooting

### LEGION Connection Issues

**Problem**: LegionBridge cannot reach LEGION endpoint
```
Solution:
1. Verify LEGION_ENDPOINT in .env
2. Check firewall rules
3. Verify LEGION_API_KEY
4. Check LEGION server health: GET /api/legion/defender/status
```

### Threat Correlation Not Working

**Problem**: No entries in legion_correlations table
```
Solution:
1. Verify LEGION_ENABLED=true in .env
2. Verify LEGION_AUTO_CORRELATE=true
3. Check logs for errors
4. Run: php scheduler-task.php
```

### High False Positive Rate

**Problem**: Too many low-confidence threats escalating
```
Solution:
1. Increase LEGION_CORRELATION_THRESHOLD (default: 0.7)
2. Increase LEGION_THREAT_THRESHOLD (default: 3)
3. Review LEGION provider calibration
4. Check agent result quality
```

### Containment Not Executing

**Problem**: Automated response not triggering
```
Solution:
1. Verify LEGION_INTEGRATION_MODE=active
2. Check firewall permissions for containment actions
3. Verify pfSense XML-RPC API access
4. Check logs for containment errors
```

## Security Considerations

1. **API Authentication**: Always use LEGION_API_KEY
2. **Webhook Security**: Use HTTPS for SECURITY_WEBHOOK_URL
3. **Alert Email**: Verify SECURITY_ALERT_EMAIL is monitored
4. **Mode Setting**: Use 'passive' mode initially, transition to 'active' after validation
5. **Rate Limiting**: Implement rate limits on threat analysis endpoints
6. **Audit Logging**: Review legion_analysis and legion_correlations regularly
7. **Backup**: Regular database backups of threat history

## Performance Optimization

### Database Indexing

```sql
CREATE INDEX idx_agent_execution ON legion_correlations(agent_id, execution_id);
CREATE INDEX idx_threat_level ON legion_analysis(threat_level, created_at);
CREATE INDEX idx_correlation_score ON legion_correlations(correlation_score);
```

### Cache Configuration

```env
# Threat intelligence cache
LEGION_CACHE_TTL=3600

# Consider Redis for distributed caching
REDIS_HOST=localhost
REDIS_PORT=6379
```

### Batch Processing

For large-scale deployments:
- Queue threat analysis jobs
- Batch correlate multiple executions
- Asynchronous alert delivery

## Support and Resources

- **Documentation**: `/docs/legion_integration.md`
- **API Reference**: `/api/docs`
- **Dashboard**: `/unified-dashboard.html`
- **Status Check**: `/DEPLOYMENT_STATUS.php`
- **Logs**: `/var/log/pfsense-ai-manager/`

## Version History

- **v1.0** - Initial LEGION integration
  - LegionBridge implementation
  - ThreatHandler escalation engine
  - Unified dashboard
  - Automated correlation

## License

Part of pfSense AI Manager
Licensed under Apache 2.0
