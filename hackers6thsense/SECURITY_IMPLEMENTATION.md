# Security Implementation Guide

## Overview
This document outlines the security hardening implemented in the Hackers6thSense application. All changes follow OWASP security standards and best practices.

## 1. Authentication & Authorization

### Implementation
- **Location**: `src/Auth/AuthMiddleware.php`
- **Method**: Bearer token authentication with API keys
- **CORS**: Restricted to configurable allowed origins (no wildcard)

### Configuration
```php
// .env
API_KEY=your_secure_api_key_minimum_32_chars
ALLOWED_ORIGINS=http://localhost:3000,https://yourdomain.com
```

### How It Works
1. All non-public endpoints require an `Authorization: Bearer {API_KEY}` header
2. Public endpoints: `/api/system/status`, `/api/system/providers`
3. CORS headers only added for requests from allowed origins
4. Origin validation prevents cross-site attacks

### Future Improvements
- Implement JWT tokens with expiration
- Add OAuth2 support for multi-tenant scenarios
- Implement role-based access control (RBAC)

---

## 2. Input Validation

### Implementation
- **Location**: `src/Utils/Validator.php`
- **Methods**: Strict parameter validation across all endpoints

### Validation Rules

| Parameter | Validation | Max Length | Allowed Values |
|-----------|-----------|-----------|-----------------|
| `timeframe` | Enum validation | - | `last_hour`, `last_24_hours`, `last_7_days`, `last_30_days`, `custom` |
| `limit` | Integer 1-1000 | - | Numeric |
| `offset` | Integer 0-∞ | - | Numeric |
| `filter` | Alphanumeric + operators | 500 chars | Filtered characters |
| `query` | Alphanumeric + punctuation | 1000 chars | Filtered characters |
| `port` | Integer 1-65535 | - | Valid port range |
| `ip` | FILTER_VALIDATE_IP | - | Valid IPv4/IPv6 |
| `analysis_type` | Enum validation | - | `traffic`, `threat`, `config`, `log`, `anomaly` |

### Prevention Mechanisms
- **Length limits**: Prevent buffer overflow and DoS attacks
- **Type validation**: Integer validators prevent type confusion
- **Character filtering**: Removes potentially dangerous characters
- **Enum validation**: Only allows predefined values
- **IP/Port validation**: Built-in PHP validators for network parameters

### Example Usage
```php
Validator::clearErrors();
$limit = Validator::validateLimit($input['limit'] ?? null);
$query = Validator::validateQuery($input['query'] ?? null);

if (Validator::hasErrors()) {
    $errorHandler->handleValidationError(Validator::getErrors());
}
```

---

## 3. Error Handling

### Implementation
- **Location**: `src/Utils/ErrorHandler.php`
- **Strategy**: Standardized error codes + server-side logging

### Error Response Format
```json
{
    "error_code": "VALIDATION_ERROR",
    "message": "Validation failed",
    "details": ["field1 is required", "field2 exceeds max length"]
}
```

### Error Codes
- `AUTH_FAILED` (401): Authentication failed
- `AUTH_REQUIRED` (401): Authentication header missing
- `FORBIDDEN` (403): Authorization denied
- `VALIDATION_ERROR` (400): Input validation failed
- `NOT_FOUND` (404): Endpoint not found
- `INTERNAL_ERROR` (500): Server error
- `SERVICE_UNAVAILABLE` (503): Service temporarily down

### Key Features
- **No stack traces to clients**: Full error details logged server-side only
- **Generic client messages**: Prevents information disclosure
- **Debug mode**: Detailed errors in development only
- **Consistent format**: All errors use standardized structure

### Configuration
```php
// .env
APP_DEBUG=false  # Set to true only in development
```

---

## 4. Secure Cache System

### Implementation
- **Location**: `src/Utils/SecureCache.php`
- **Encryption**: AES-256-GCM
- **Algorithm**: OWASP-recommended encryption

### Key Features
1. **Encrypted Storage**: All cached data encrypted before disk write
2. **Versioned Keys**: Cache keys include version hash (SHA256)
3. **Expiration Checking**: TTL validation on every retrieval
4. **Restrictive Permissions**: Cache files created with 0600 (owner-only)
5. **Cache Rotation**: Version invalidates all old cache entries

### Cache Statistics API
```php
$cache = SecureCache::getInstance();
$stats = $cache->getStats();
// Returns: {version, entries, size_bytes, size_mb}
```

### Encryption Configuration
```php
// .env
CACHE_ENCRYPTION_KEY=your_base64_encoded_32byte_key
# Generate: php -r "echo base64_encode(openssl_random_pseudo_bytes(32));"
```

### Usage
```php
$cache = SecureCache::getInstance();
$cache->set('my_key', $data, 3600); // 1 hour TTL
$value = $cache->get('my_key');
$cache->rotateVersion(); // Invalidate all cache
```

---

## 5. Secure Credential Storage

### Implementation
- **Location**: `src/Utils/Config.php`
- **Strategy**: Environment variables + filter functions

### Features
- **No hardcoded secrets**: All credentials from `.env` file
- **Type casting**: Explicit type validation (bool, int, string)
- **Silent defaults**: Uses empty strings for missing credentials
- **Audit logging**: Configuration load logged without sensitive data

### Sensitive Configuration
```php
// These should NEVER appear in logs or error messages
$config->get('pfsense.password')     // Private
$config->get('ai.providers.*.api_key') // Private
$config->get('auth.api_key')          // Private

// These are safe to log
$config->get('pfsense.host')         // Public
$config->get('app.env')              // Public
$config->get('app.debug')            // Public
```

### Best Practices
1. **Store `.env` outside web root** if possible
2. **Never commit `.env` to version control**
3. **Use strong passwords** (minimum 32 characters for API keys)
4. **Rotate credentials regularly** (quarterly recommended)
5. **Monitor configuration access** in logs

---

## 6. Security Headers

### Implementation
- **Location**: `src/bootstrap.php`
- **Headers Added**:

| Header | Value | Purpose |
|--------|-------|---------|
| `X-Content-Type-Options` | `nosniff` | Prevent MIME sniffing |
| `X-Frame-Options` | `DENY` | Prevent clickjacking |
| `X-XSS-Protection` | `1; mode=block` | Enable XSS filter |
| `Strict-Transport-Security` | `max-age=31536000` | Force HTTPS |
| `Content-Security-Policy` | `default-src 'self'` | Restrict resource loading |

### Response Encoding
- **Type**: `application/json; charset=utf-8`
- **JSON Flags**: `JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP`
- **Purpose**: Prevent JSON injection and XSS

---

## 7. Updated Endpoints

All endpoints now follow a consistent security pattern:

```php
public function myEndpoint()
{
    try {
        // 1. Validate all input
        Validator::clearErrors();
        $param1 = Validator::validate...($input['param1'] ?? null);
        
        if (Validator::hasErrors()) {
            $this->errorHandler->handleValidationError(Validator::getErrors());
        }

        // 2. Process request
        $result = $this->analyzer->analyze($param1);

        // 3. Return standardized response
        self::response(['data' => $result, 'success' => true]);
        
    } catch (\Exception $e) {
        // 4. Handle exceptions with error handler
        $this->errorHandler->handleException($e, 'EndpointName::myEndpoint');
    }
}
```

### Updated Endpoints
- ✅ `AnalysisEndpoint` - Validates timeframe, hours, limits
- ✅ `LogEndpoint` - Validates filter, query, limit, offset
- ✅ `ChatEndpoint` - Validates message, conversation_id, limit
- ✅ `ThreatEndpoint` - Validates threat data
- ✅ `ConfigEndpoint` - Validates analysis type
- ✅ `SystemEndpoint` - Public endpoints, minimal validation

---

## 8. Environment Configuration Template

```bash
# Copy to .env and configure with your values
cp .env.example .env

# Generate strong API key
php -r "echo 'API_KEY=' . bin2hex(random_bytes(32)) . PHP_EOL;"

# Generate cache encryption key
php -r "echo 'CACHE_ENCRYPTION_KEY=' . base64_encode(openssl_random_pseudo_bytes(32)) . PHP_EOL;"

# Set permissions
chmod 600 .env
chmod 700 storage/cache
```

---

## 9. Testing Security

### Manual Testing

```bash
# Test 1: Missing API key
curl http://localhost/api/threats

# Expected: 401 Unauthorized
{
    "error_code": "AUTH_REQUIRED",
    "message": "Authentication required"
}

# Test 2: Invalid timeframe (validation)
curl -H "Authorization: Bearer YOUR_API_KEY" \
     -X POST \
     -d '{"timeframe":"invalid_value"}' \
     http://localhost/api/analysis/traffic

# Expected: 400 Bad Request with validation errors

# Test 3: SQL injection attempt (should be filtered)
curl -H "Authorization: Bearer YOUR_API_KEY" \
     -X GET \
     "http://localhost/api/logs?filter='; DROP TABLE logs; --"

# Expected: 400 Validation error or empty results (filtered)

# Test 4: CORS check (unauthorized origin)
curl -H "Origin: https://malicious.com" \
     -H "Authorization: Bearer YOUR_API_KEY" \
     http://localhost/api/threats

# Expected: No Access-Control-Allow-Origin header
```

---

## 10. Migration from Old Code

### Breaking Changes
1. **Response format changed**: Responses now include `success` key
2. **Error format changed**: Errors include `error_code`
3. **Authentication required**: All endpoints except system require API key
4. **Validation stricter**: Invalid parameters rejected immediately

### Migration Steps
```php
// Old endpoint (deprecated)
$result = $analyzer->analyze($input['filter']);

// New endpoint (secure)
Validator::validateFilter($input['filter']);
if (Validator::hasErrors()) {
    $errorHandler->handleValidationError(Validator::getErrors());
}
$result = $analyzer->analyze($input['filter']);
```

---

## 11. Logging & Monitoring

### What Gets Logged
- ✅ Authentication failures (with timestamp, origin IP)
- ✅ Authorization denials
- ✅ Validation errors
- ✅ Unhandled exceptions (without stack traces exposed to clients)
- ✅ Configuration load status

### What Does NOT Get Logged
- ❌ API key values
- ❌ Password values
- ❌ Full stack traces (server-side only)
- ❌ Sensitive user query content (sanitized versions only)

### Log File Location
```
storage/logs/pfsense-ai.log
```

### Log Level Configuration
```php
// .env
APP_LOG_LEVEL=info  # debug, info, warning, error, critical
```

---

## 12. Compliance & Standards

### Standards Followed
- ✅ **OWASP Top 10**: Addresses injection, broken auth, XSS
- ✅ **CWE-20**: Input validation
- ✅ **CWE-22**: Path traversal prevention
- ✅ **CWE-94**: Code injection prevention
- ✅ **RFC 7231**: HTTP semantics and content negotiation

### Regular Security Audits
- Review logs weekly for anomalies
- Update dependencies monthly
- Rotate API keys quarterly
- Perform penetration testing before major releases

---

## 13. Incident Response

### If Credentials Compromised
1. **Rotate API key**: Generate new API_KEY in .env
2. **Rotate cache encryption key**: Generate new CACHE_ENCRYPTION_KEY
3. **Flush cache**: `$cache->flush()` or restart application
4. **Review logs**: Check for unauthorized access
5. **Notify users**: Inform affected systems

### If Data Breach Suspected
1. **Enable debug mode**: Set APP_DEBUG=true temporarily
2. **Increase log level**: Set APP_LOG_LEVEL=debug
3. **Review logs**: `tail -f storage/logs/pfsense-ai.log`
4. **Disable compromised endpoints**: Comment out routes
5. **Investigate**: Check access patterns for anomalies

---

## Questions & Support

For security concerns:
1. Review this documentation
2. Check `.env.example` for configuration
3. Review `src/Utils/Validator.php` for validation rules
4. Review `src/Utils/ErrorHandler.php` for error handling
5. Check logs at `storage/logs/pfsense-ai.log`
