# Oblivion Integration Guide

## Overview

This document describes the integration of the **Oblivion cyber range platform** with Hackers6thSense. Oblivion provides adversary emulation and cyber training capabilities that enhance your security posture testing and incident response training.

## What is Oblivion?

Oblivion is a containerized cyber range platform that enables:
- 🎯 **Adversary Emulation** - Simulate real-world attack scenarios
- 🧪 **Hands-on Training** - Practice incident response procedures
- 📊 **Red Team Operations** - Execute controlled penetration testing
- 🔐 **Security Assessment** - Validate defensive capabilities
- 📈 **Metrics Collection** - Track training effectiveness

## Architecture

### Integration Components

```
┌─────────────────────────────────────────────────────────────┐
│                  Hackers6thSense                            │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────┐         ┌──────────────────┐         │
│  │  Chat API        │         │  Threat Intel    │         │
│  │  & Analysis      │────────→│  & Analysis      │         │
│  └──────────────────┘         └──────────────────┘         │
│           ▲                            ▲                    │
│           │                            │                    │
│           └────────────┬───────────────┘                    │
│                        │                                    │
│              ┌─────────▼────────────┐                      │
│              │ Oblivion Endpoint    │                      │
│              │  & API Router        │                      │
│              └─────────┬────────────┘                      │
└───────────────────────┼──────────────────────────────────┘
                        │
                        │ HTTP/WebSocket
                        │
┌───────────────────────▼──────────────────────────────────┐
│            Oblivion Cyber Range Platform                  │
├────────────────────────────────────────────────────────┤
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  │
│  │  Scenarios   │  │  Attacks     │  │  Assets      │  │
│  │  Manager     │  │  Module      │  │  Store       │  │
│  └──────────────┘  └──────────────┘  └──────────────┘  │
│                                                        │
│  ┌──────────────────────────────────────────────────┐ │
│  │  Dashboard & Monitoring                         │ │
│  └──────────────────────────────────────────────────┘ │
└────────────────────────────────────────────────────────┘
```

### Key Integration Points

1. **Scenario Management** - Start, stop, and monitor cyber training scenarios
2. **Attack Execution** - Execute controlled attacks for testing
3. **Asset Management** - Define and manage test targets
4. **Event Integration** - Capture Oblivion events in pfSense dashboards
5. **Policy Enforcement** - Validate that attacks respect engagement policies

## Getting Started

### Prerequisites

1. **Oblivion Installation**
   ```bash
   # Clone Oblivion repository
   git clone https://github.com/your-org/Oblivion.git
   cd Oblivion
   
   # Build Docker image
   docker build -t oblivion:latest .
   
   # Run container
   docker run -d \
     --name oblivion \
     -p 8080:8000 \
     -e MISTRAL_API_KEY=your_key \
     -e OBLIVION_EXECUTION_MODE=simulation \
     oblivion:latest
   ```

2. **Environment Variables**
   ```bash
   # .env file
   OBLIVION_ENABLED=true
   OBLIVION_BASE_URL=http://localhost:8080
   OBLIVION_AUTH_TOKEN=your_auth_token
   OBLIVION_AUTO_DEPLOY=false
   OBLIVION_MAX_CONCURRENT=5
   MISTRAL_API_KEY=your_mistral_key
   OBLIVION_POLICY_FILE=/path/to/policy.yaml
   ```

### Installation

1. **Update Router Configuration**
   
   The router has been automatically updated with Oblivion endpoints (see `/src/API/Router.php`).

2. **Configure Oblivion Settings**
   
   Edit `/src/config/oblivion.config.php` or set environment variables:
   ```php
   // Service Configuration
   'service' => [
       'enabled' => true,
       'base_url' => 'http://localhost:8080',
       'timeout' => 30,
   ]
   ```

3. **Ensure Directories Exist**
   ```bash
   mkdir -p /var/log/oblivion
   mkdir -p /var/cache/oblivion
   chmod 755 /var/log/oblivion
   ```

## API Endpoints

### Session & Planning

#### 1. Start Oblivion Session
**POST** `/api/oblivion/session/start`

Initialize a new Oblivion attack simulation session with AI planning.

```json
{
  "agent_id": 5,
  "agent_type": "red_team",
  "target_params": {
    "target_host": "192.168.1.10",
    "target_type": "web_server",
    "difficulty": "medium"
  }
}
```

**Response:**
```json
{
  "status": "success",
  "session_id": "sess_abc123xyz",
  "agent_id": 5,
  "start_time": "2024-01-15T10:30:00Z",
  "configuration": {
    "execution_mode": "simulation",
    "max_concurrent": 5,
    "timeout": 3600
  }
}
```

#### 2. Generate Attack Plan
**POST** `/api/oblivion/plan`

Generate an AI-powered attack plan using Mistral AI.

```json
{
  "goal": "Test network perimeter security and identify vulnerabilities",
  "constraints": {
    "timeframe": 3600,
    "methods": ["reconnaissance", "exploitation"],
    "scope": ["192.168.1.0/24"],
    "avoid": ["critical_systems"]
  }
}
```

**Response:**
```json
{
  "status": "success",
  "plan_id": "plan_001",
  "goal": "Test network perimeter security...",
  "steps": [
    {
      "order": 1,
      "attack_type": "reconnaissance",
      "description": "Network scanning and enumeration",
      "tools": ["nmap", "nessus"],
      "estimated_duration": 300
    },
    {
      "order": 2,
      "attack_type": "exploitation",
      "description": "Attempt known vulnerability exploitation",
      "tools": ["metasploit"],
      "estimated_duration": 600
    }
  ],
  "total_estimated_duration": 900,
  "policy_compliant": true
}
```

#### 3. Get Status
**GET** `/api/oblivion/status`

Check Oblivion integration status and configuration.

```json
{
  "status": "success",
  "enabled": true,
  "configuration": {
    "execution_mode": "simulation",
    "mistral_key_set": true,
    "phishing_enabled": true,
    "disinformation_enabled": false,
    "max_concurrent": 5
  }
}
```

### Attack Execution

#### 1. Execute DDoS Simulation
**POST** `/api/oblivion/attack/ddos`

Simulate a Distributed Denial of Service attack.

```json
{
  "target_host": "192.168.1.10",
  "duration": 60,
  "threads": 10,
  "protocol": "http"
}
```

**Response:**
```json
{
  "status": "success",
  "attack_id": "ddos_001",
  "target": "192.168.1.10",
  "status": "running",
  "packets_sent": 1250,
  "bandwidth_used": 12.5,
  "start_time": "2024-01-15T10:35:00Z"
}
```

#### 2. Execute SQL Injection
**POST** `/api/oblivion/attack/sqli`

Simulate SQL injection attacks against web applications.

```json
{
  "target_url": "http://192.168.1.10/app/login",
  "payloads": [
    "' OR '1'='1",
    "admin'--",
    "1' UNION SELECT NULL--"
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "attack_id": "sqli_001",
  "target_url": "http://192.168.1.10/app/login",
  "payloads_tested": 3,
  "vulnerable": true,
  "findings": [
    {
      "payload": "' OR '1'='1",
      "response_code": 200,
      "data_leaked": true
    }
  ]
}
```

#### 3. Execute Brute Force
**POST** `/api/oblivion/attack/bruteforce`

Simulate credential brute force attacks.

```json
{
  "target_service": "ssh",
  "credentials": [
    {"username": "admin", "password": "password"},
    {"username": "admin", "password": "admin"},
    {"username": "root", "password": "toor"}
  ]
}
```

**Response:**
```json
{
  "status": "success",
  "attack_id": "bf_001",
  "target_service": "ssh",
  "attempts": 3,
  "successful": 1,
  "credentials_found": [
    {"username": "admin", "password": "password"}
  ]
}
```

#### 4. Execute Ransomware Simulation
**POST** `/api/oblivion/attack/ransomware`

Simulate ransomware attack on file systems.

```json
{
  "target_path": "/mnt/test_files",
  "file_count": 100
}
```

**Response:**
```json
{
  "status": "success",
  "attack_id": "ransomware_001",
  "target_path": "/mnt/test_files",
  "files_encrypted": 98,
  "encryption_success_rate": 0.98
}
```

#### 5. Execute Metasploit Module
**POST** `/api/oblivion/attack/metasploit`

Execute Metasploit exploit simulations.

```json
{
  "target_host": "192.168.1.10",
  "exploit_module": "exploit/windows/smb/ms17_010_eternalblue"
}
```

**Response:**
```json
{
  "status": "success",
  "attack_id": "msf_001",
  "exploit": "exploit/windows/smb/ms17_010_eternalblue",
  "target": "192.168.1.10",
  "payload_delivered": true,
  "shell_opened": true
}
```

### Social Engineering

#### 1. Generate Phishing Email
**POST** `/api/oblivion/phishing/generate`

Generate realistic phishing email simulations.

```json
{
  "organization": "Acme Corporation",
  "pretext": "IT Security Update Required"
}
```

**Response:**
```json
{
  "status": "success",
  "phishing_id": "phish_001",
  "organization": "Acme Corporation",
  "subject": "URGENT: Security Update Required - Action Needed",
  "body": "Dear Employee...",
  "sender": "security-team@acme-corp.com",
  "link": "https://secure-acme.verify.com/login",
  "likelihood_of_click": 0.45
}
```

#### 2. Generate Disinformation Content
**POST** `/api/oblivion/disinformation/generate`

Generate disinformation and social engineering content.

```json
{
  "topic": "CEO resignation announcement",
  "context": {
    "company": "TechCorp",
    "tone": "urgent"
  }
}
```

**Response:**
```json
{
  "status": "success",
  "disinformation_id": "disinfo_001",
  "content": "Breaking News: TechCorp CEO unexpectedly resigns...",
  "platform": "twitter_fake_account",
  "credibility_score": 0.78,
  "potential_reach": 50000
}
```

### Statistics & Monitoring

#### 1. Get Attack Statistics
**GET** `/api/oblivion/statistics`

Retrieve comprehensive attack simulation statistics.

```json
{
  "status": "success",
  "statistics": {
    "total_attacks": 156,
    "attacks_today": 23,
    "success_rate": 0.68,
    "avg_duration": 420,
    "attack_types": {
      "ddos": 45,
      "reconnaissance": 38,
      "exploitation": 32,
      "phishing": 25,
      "bruteforce": 16
    },
    "most_vulnerable_targets": [
      {
        "host": "192.168.1.15",
        "vulnerabilities_found": 8,
        "successful_exploits": 4
      }
    ]
  }
}
```

#### 2. Get Recent Attacks
**GET** `/api/oblivion/attacks/recent?limit=50`

Retrieve recent attack execution history.

```json
{
  "status": "success",
  "attacks": [
    {
      "attack_id": "ddos_156",
      "type": "ddos",
      "target": "192.168.1.20",
      "status": "completed",
      "start_time": "2024-01-15T11:15:00Z",
      "end_time": "2024-01-15T11:16:15Z",
      "success": true
    },
    {
      "attack_id": "msf_155",
      "type": "metasploit",
      "target": "192.168.1.10",
      "status": "completed",
      "start_time": "2024-01-15T11:10:00Z",
      "end_time": "2024-01-15T11:14:30Z",
      "success": true
    }
  ]
}

## Integration Examples

### Example 1: Running a DDoS Simulation

```php
<?php

use PfSenseAI\API\Endpoints\OblivionEndpoint;

$endpoint = new OblivionEndpoint();

// Execute a DDoS simulation
$result = $endpoint->executeDDoS([
    'target_host' => '192.168.1.10',
    'duration' => 60,
    'threads' => 10
]);

echo "Attack ID: " . $result['attack_id'] . "\n";
echo "Status: " . $result['status'] . "\n";
echo "Packets sent: " . $result['packets_sent'] . "\n";
```

### Example 2: Generating an Attack Plan

```php
<?php

use PfSenseAI\API\Endpoints\OblivionEndpoint;

$endpoint = new OblivionEndpoint();

// Generate AI-powered attack plan
$plan = $endpoint->generatePlan([
    'goal' => 'Test network perimeter security',
    'constraints' => [
        'timeframe' => 3600,
        'methods' => ['reconnaissance', 'exploitation'],
        'scope' => ['192.168.1.0/24']
    ]
]);

foreach ($plan['steps'] as $step) {
    echo "Step {$step['order']}: {$step['description']}\n";
    echo "  Tools: " . implode(", ", $step['tools']) . "\n";
    echo "  Duration: {$step['estimated_duration']}s\n";
}
```

### Example 3: Starting an Oblivion Session

```php
<?php

use PfSenseAI\API\Endpoints\OblivionEndpoint;

$endpoint = new OblivionEndpoint();

// Start a new session
$session = $endpoint->startSession([
    'agent_id' => 5,
    'agent_type' => 'red_team',
    'target_params' => [
        'target_host' => '192.168.1.10',
        'difficulty' => 'medium'
    ]
]);

echo "Session started: " . $session['session_id'] . "\n";
echo "Mode: " . $session['configuration']['execution_mode'] . "\n";
```

### Example 4: Phishing Simulation

```php
<?php

use PfSenseAI\API\Endpoints\OblivionEndpoint;

$endpoint = new OblivionEndpoint();

// Generate phishing email
$phishing = $endpoint->generatePhishing([
    'organization' => 'TechCorp Inc',
    'pretext' => 'Annual Security Update Required'
]);

echo "Subject: " . $phishing['subject'] . "\n";
echo "Likelihood of click: " . ($phishing['likelihood_of_click'] * 100) . "%\n";
```

### Example 5: API Usage in Chat Integration

When users interact with the chat interface asking about attack simulations:

```javascript
// Frontend JavaScript
fetch('/api/oblivion/attack/ddos', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        target_host: '192.168.1.10',
        duration: 60,
        threads: 10
    })
})
.then(response => response.json())
.then(data => {
    console.log('Attack running:', data);
    updateDashboard(data);
});
```

## Configuration

### Environment Variables

```bash
# Enable/Disable Oblivion integration
OBLIVION_ENABLED=true

# Base URL of Oblivion API
OBLIVION_BASE_URL=http://localhost:8080

# Authentication
OBLIVION_AUTH_TYPE=bearer
OBLIVION_AUTH_TOKEN=your_token_here

# Scenario settings
OBLIVION_AUTO_DEPLOY=false
OBLIVION_MAX_CONCURRENT=5
OBLIVION_DEFAULT_DURATION=3600

# Attack execution
OBLIVION_EXECUTION_MODE=simulation
OBLIVION_MAX_PARALLEL=3

# Assets
OBLIVION_AUTO_DISCOVERY=true
OBLIVION_SYNC_INTERVAL=300

# Policy
OBLIVION_POLICY_VALIDATION=true
OBLIVION_POLICY_FILE=/path/to/policy.yaml
OBLIVION_STRICT_MODE=false

# Integration
OBLIVION_SYNC_THREAT_INTEL=true
OBLIVION_SEND_ALERTS_CHAT=true
OBLIVION_EXPORT_LEGION=true

# Logging
OBLIVION_LOGGING_ENABLED=true
OBLIVION_LOG_EVENTS=true
OBLIVION_LOG_LEVEL=INFO
```

### Configuration File

Edit `/src/config/oblivion.config.php` to customize settings.

## Monitoring & Analytics

### View Oblivion Dashboard

Access the integrated dashboard at:
```
http://localhost:8000/dashboard.html?view=oblivion
```

### Metrics Tracked

- **Scenario Metrics**
  - Total scenarios executed
  - Average scenario duration
  - Participant count
  - Completion rate

- **Attack Metrics**
  - Attack types executed
  - Success rate
  - Detection rate
  - Average detection time

- **Team Performance**
  - Blue team detection effectiveness
  - Response time to incidents
  - Vulnerability exploitation prevention
  - Training progress

### Integration with Threat Intelligence

Oblivion events are automatically exported to:
- pfSense threat dashboard
- LEGION threat intelligence
- Chat conversation context
- Security alerts system

## Security Considerations

### 1. Policy Enforcement

Always validate attacks against your engagement policy:

```php
$client = new OblivionClient();
$result = $client->validatePolicy($attackPlan);

if (!$result['valid']) {
    throw new Exception("Attack plan violates policy: " . 
        implode(", ", $result['violations']));
}
```

### 2. Execution Modes

Use appropriate execution modes:

- **Simulation** - No actual exploitation (safe for demos)
- **Training** - Real attacks in controlled lab environment
- **Assessment** - Full penetration testing (authorized only)

### 3. Access Control

Restrict Oblivion endpoint access:

```php
// In API endpoints
if (!$this->authorizationManager->isAuthorized('oblivion.execute')) {
    return $this->errorResponse('Unauthorized', 403);
}
```

### 4. Compliance

Maintain compliance with:
- Rules of Engagement (RoE)
- Legal authorization documentation
- Incident response procedures
- Data privacy regulations

## Troubleshooting

### Connection Issues

```bash
# Test Oblivion connectivity
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/api/v1/health

# Check logs
tail -f /var/log/oblivion/error.log
```

### Policy Validation Failures

```php
// Detailed policy validation
$result = $client->validatePolicy($plan);
echo json_encode($result['violations'], JSON_PRETTY_PRINT);
```

### Scenario Not Starting

1. Check Oblivion service status
2. Verify assets are accessible
3. Validate policy compliance
4. Check resource availability

## Best Practices

1. **Schedule Scenarios During Off-Hours** - Minimize impact on production systems
2. **Use Simulation Mode for Training** - Before conducting real attacks
3. **Monitor All Attacks** - Maintain visibility of all adversary activities
4. **Document Results** - Keep detailed records for training effectiveness
5. **Iterate on Feedback** - Continuously improve your red team exercises
6. **Comply with Policies** - Always respect rules of engagement

## Additional Resources

- [Oblivion GitHub Repository](https://github.com/your-org/Oblivion)
- [Oblivion Documentation](../Oblivion-main/docs/index.md)
- [ATTACK Framework](https://attack.mitre.org/)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)

## Support

For issues or questions:

1. Check the [Oblivion Documentation](../Oblivion-main/docs/index.md)
2. Review [Troubleshooting Guide](#troubleshooting)
3. Check application logs: `/var/log/pfsense-ai-manager/`
4. Contact your security team

---

**Last Updated:** January 2024  
**Version:** 1.0.0  
**Status:** Production Ready
