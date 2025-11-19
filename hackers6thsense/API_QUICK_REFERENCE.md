# API Quick Reference

## Complete Endpoint Listing

### Analysis Endpoints
- `POST /api/analysis/traffic` - Analyze network traffic patterns
- `GET /api/analysis/traffic/history` - Get traffic history
- `GET /api/analysis/anomalies` - Detect network anomalies

### Threat Management
- `GET /api/threats` - List threats
- `POST /api/threats/analyze` - Analyze threat
- `GET /api/threats/dashboard` - Threat dashboard

### Configuration
- `GET /api/config/rules` - Get firewall rules
- `POST /api/config/analyze` - Analyze configuration
- `GET /api/recommendations` - Get recommendations

### Logs
- `GET /api/logs` - Get logs
- `POST /api/logs/analyze` - Analyze logs
- `POST /api/logs/search` - Search logs
- `GET /api/logs/patterns` - Get log patterns

### Chat (Enhanced)
- `POST /api/chat` - Send message
- `POST /api/chat/multi-turn` - Multi-turn conversation
- `GET /api/chat/history` - Get conversation history
- `GET /api/chat/summary` - Get conversation summary
- `POST /api/chat/clear` - Clear history

### System
- `GET /api/system/status` - System status
- `GET /api/system/providers` - List AI providers

### Agents
- `GET /api/agents` - List agents
- `GET /api/agents/:id` - Get agent details
- `POST /api/agents/:id/execute` - Execute agent
- `POST /api/agents/batch/execute` - Batch execute
- `GET /api/agents/:id/results` - Get agent results
- `GET /api/agents/active` - List active agents
- `POST /api/agents/:id/stop` - Stop agent
- `GET /api/agents/stats` - Agent statistics

### Scheduling
- `POST /api/schedules` - Create schedule
- `GET /api/schedules` - List schedules
- `GET /api/schedules/:id` - Get schedule
- `PUT /api/schedules/:id` - Update schedule
- `DELETE /api/schedules/:id` - Delete schedule
- `GET /api/schedules/history` - Execution history
- `POST /api/schedules/execute` - Execute scheduled jobs
- `GET /api/schedules/stats` - Schedule statistics

### Filters
- `POST /api/filters` - Create filter
- `GET /api/filters` - List filters
- `POST /api/filters/apply` - Apply filter
- `DELETE /api/filters/:id` - Delete filter

### LEGION Integration
- `POST /api/legion/defender/start` - Start defender
- `POST /api/legion/analyze` - Analyze threat
- `POST /api/legion/recommendations` - Get recommendations
- `POST /api/legion/correlate` - Correlate threat intelligence
- `GET /api/legion/threat-intel` - Threat intelligence
- `GET /api/legion/defender/status` - Defender status
- `POST /api/legion/alerts` - Send alerts
- `GET /api/legion/analytics` - Analytics

### Oblivion Integration - Session & Planning
- `POST /api/oblivion/session/start` - Start attack session
- `POST /api/oblivion/plan` - Generate attack plan
- `GET /api/oblivion/status` - Integration status

### Oblivion Integration - Attack Execution
- `POST /api/oblivion/attack/ddos` - Execute DDoS
- `POST /api/oblivion/attack/sqli` - Execute SQL injection
- `POST /api/oblivion/attack/bruteforce` - Execute brute force
- `POST /api/oblivion/attack/ransomware` - Execute ransomware simulation
- `POST /api/oblivion/attack/metasploit` - Execute Metasploit module

### Oblivion Integration - Social Engineering
- `POST /api/oblivion/phishing/generate` - Generate phishing email
- `POST /api/oblivion/disinformation/generate` - Generate disinformation

### Oblivion Integration - Monitoring
- `GET /api/oblivion/statistics` - Attack statistics
- `GET /api/oblivion/attacks/recent` - Recent attacks

## Common Response Format

### Success Response
```json
{
  "status": "success",
  "data": {...},
  "timestamp": "2024-01-15T10:30:00Z"
}
```

### Error Response
```json
{
  "status": "error",
  "message": "Error description",
  "code": "ERROR_CODE",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

## Authentication

All endpoints require authentication. Include header:
```
Authorization: Bearer YOUR_TOKEN
```

## Rate Limiting

- Standard rate limit: 1000 requests per hour
- Batch endpoints: 100 requests per hour
- Each rate limit window resets hourly

## Error Codes

- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `429` - Rate Limited
- `500` - Internal Server Error

## Quick Usage Examples

### Using cURL
```bash
# Get system status
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8000/api/system/status

# Send chat message
curl -X POST http://localhost:8000/api/chat \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"message":"Analyze my firewall"}'
```

### Using JavaScript
```javascript
// Fetch API
const response = await fetch('/api/system/status', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN'
  }
});
const data = await response.json();
console.log(data);
```

### Using PHP
```php
<?php
$ch = curl_init('http://localhost:8000/api/system/status');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer YOUR_TOKEN',
    'Content-Type: application/json'
]);
$response = curl_exec($ch);
$data = json_decode($response, true);
?>
```

## Webhook Support

Some endpoints support webhooks:
- `POST /api/webhooks/events` - Register webhook
- `DELETE /api/webhooks/:id` - Unregister webhook

## API Documentation

For detailed documentation, see:
- [Enhanced Chat API](ENHANCED_CHAT_API.md)
- [API Reference](API.md)
- [Oblivion Integration](OBLIVION_INTEGRATION.md)

---

**Last Updated:** January 2024
