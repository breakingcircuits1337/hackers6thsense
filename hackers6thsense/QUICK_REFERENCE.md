# Quick Reference Guide - Security Implementation

## 🔐 Core Security Classes

### Validator - Input Validation
```php
use PfSenseAI\Utils\Validator;

// Clear previous errors
Validator::clearErrors();

// Validate parameters
$timeframe = Validator::validateTimeframe($input['timeframe'] ?? null);
$limit = Validator::validateLimit($input['limit'] ?? null);
$query = Validator::validateQuery($input['query'] ?? null, 1000);
$ip = Validator::validateIp($input['ip'] ?? null);

// Check for validation errors
if (Validator::hasErrors()) {
    $errors = Validator::getErrors();  // Get array of errors
    $errorHandler->handleValidationError($errors);
}
```

### AuthMiddleware - Authentication
```php
use PfSenseAI\Auth\AuthMiddleware;

$auth = new AuthMiddleware();

// Check if request is authenticated
if (!$auth->authenticate()) {
    $errorHandler->handleAuthError('Invalid token');
}

// Check CORS
if (!$auth->validateCors()) {
    $errorHandler->handleAuthorizationError('CORS rejected');
}
```

### ErrorHandler - Standardized Errors
```php
use PfSenseAI\Utils\ErrorHandler;

$handler = new ErrorHandler();

// Validation errors
$handler->handleValidationError(['field is required']);

// Authentication errors
$handler->handleAuthError('Token expired');

// Authorization errors
$handler->handleAuthorizationError('Insufficient permissions');

// Generic exceptions
try {
    // code
} catch (\Exception $e) {
    $handler->handleException($e, 'ClassName::methodName');
}
```

### SecureCache - Encrypted Caching
```php
use PfSenseAI\Utils\SecureCache;

$cache = SecureCache::getInstance();

// Store encrypted data
$cache->set('my_key', $data, 3600);  // TTL in seconds

// Retrieve and decrypt
$value = $cache->get('my_key', $default);

// Check existence
if ($cache->has('my_key')) { }

// Delete
$cache->forget('my_key');

// Clear all
$cache->flush();

// Get stats
$stats = $cache->getStats();
```

---

## 📋 Endpoint Pattern (Standard)

```php
<?php
namespace PfSenseAI\API\Endpoints;

use PfSenseAI\API\Router;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Utils\ErrorHandler;

class MyEndpoint extends Router
{
    private $errorHandler;

    public function __construct()
    {
        parent::__construct();
        $this->errorHandler = new ErrorHandler();
    }

    public function myAction()
    {
        try {
            // 1. Validate input
            $input = self::getInput();
            Validator::clearErrors();
            
            $param1 = Validator::validateFilter($input['param1'] ?? null);
            $param2 = Validator::validateInteger($input['param2'] ?? null);
            
            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            // 2. Process
            $result = $this->doSomething($param1, $param2);

            // 3. Return success
            self::response(['data' => $result, 'success' => true]);
            
        } catch (\Exception $e) {
            // 4. Handle error
            $this->errorHandler->handleException($e, 'MyEndpoint::myAction');
        }
    }
}
```

---

## 🔑 Environment Configuration

```bash
# .env file (KEEP SECURE! chmod 600)

# Security
API_KEY=generate_with_bin2hex(random_bytes(32))
ALLOWED_ORIGINS=http://localhost:3000,https://yourdomain.com
CACHE_ENCRYPTION_KEY=base64(openssl_random_pseudo_bytes(32))

# Application
APP_ENV=production
APP_DEBUG=false
APP_LOG_LEVEL=info

# pfSense
PFSENSE_HOST=192.168.1.1
PFSENSE_USERNAME=admin
PFSENSE_PASSWORD=your_password
PFSENSE_VERIFY_SSL=false

# AI Providers
PRIMARY_AI_PROVIDER=mistral
MISTRAL_API_KEY=your_key
GROQ_API_KEY=your_key
GEMINI_API_KEY=your_key
```

---

## 🧪 Testing Examples

### Test Missing Auth
```bash
curl -i http://localhost/api/threats
# ❌ 401 Unauthorized
```

### Test With Valid Auth
```bash
curl -i -H "Authorization: Bearer YOUR_API_KEY" \
     http://localhost/api/threats
# ✅ 200 OK
```

### Test Validation Error
```bash
curl -i -H "Authorization: Bearer YOUR_API_KEY" \
     -X GET "http://localhost/api/logs?limit=999999"
# ❌ 400 Bad Request - exceeds max
```

### Test Invalid Origin (CORS)
```bash
curl -i -H "Origin: https://evil.com" \
     -H "Authorization: Bearer YOUR_API_KEY" \
     http://localhost/api/threats
# ✅ Response but NO Access-Control-Allow-Origin header
```

---

## ⚠️ Common Mistakes to Avoid

### ❌ DON'T
```php
// Don't use unvalidated input
$limit = $_GET['limit'];  // DANGEROUS!

// Don't expose exceptions to client
echo json_encode(['error' => $e->getMessage()]);  // Exposes stack trace!

// Don't allow wildcard CORS
header('Access-Control-Allow-Origin: *');  // INSECURE!

// Don't store credentials
define('API_KEY', 'secret123');  // EXPOSED!

// Don't mix old and new patterns
self::response(['error' => $msg]);  // Old format in new code
```

### ✅ DO
```php
// Do validate all input
$limit = Validator::validateLimit($_GET['limit'] ?? null);

// Do use ErrorHandler
$this->errorHandler->handleException($e, 'Context');

// Do restrict CORS
// Use AuthMiddleware with ALLOWED_ORIGINS

// Do use environment variables
$key = $_ENV['API_KEY'];  // SECURE!

// Do use new response format
self::response(['data' => $result, 'success' => true]);
```

---

## 📊 Error Codes Reference

| Code | HTTP | Meaning | Example |
|------|------|---------|---------|
| `AUTH_REQUIRED` | 401 | Missing token | No Authorization header |
| `AUTH_FAILED` | 401 | Invalid token | Wrong API key |
| `FORBIDDEN` | 403 | Access denied | CORS rejection |
| `VALIDATION_ERROR` | 400 | Invalid input | limit > 1000 |
| `NOT_FOUND` | 404 | Endpoint missing | Wrong URL |
| `INTERNAL_ERROR` | 500 | Server error | Unhandled exception |
| `SERVICE_UNAVAILABLE` | 503 | Service down | AI provider offline |

---

## 🔍 Debugging

### Enable Debug Mode
```php
// .env
APP_DEBUG=true
APP_LOG_LEVEL=debug
```

### Check Logs
```bash
tail -f storage/logs/pfsense-ai.log

# Look for
[2024-11-18 10:30:45] ERROR: ...
[2024-11-18 10:30:46] WARNING: ...
```

### Test Cache
```php
$cache = SecureCache::getInstance();
$stats = $cache->getStats();
var_dump($stats);
// Shows: version, entries, size_bytes, size_mb
```

### Validate Configuration
```php
$config = Config::getInstance();
echo $config->get('app.env');      // OK to echo
echo $config->get('pfsense.host'); // OK to echo
// echo $config->get('ai.providers.mistral.api_key'); // DON'T echo!
```

---

## 📚 Documentation Files

| File | Purpose |
|------|---------|
| `SECURITY_IMPLEMENTATION.md` | Comprehensive security guide (13 sections) |
| `IMPLEMENTATION_SUMMARY.md` | What was changed and why |
| `.env.example` | Environment configuration template |
| This file | Quick reference for developers |

---

## 🚀 Deployment Checklist

- [ ] Copy .env.example to .env
- [ ] Generate and set API_KEY (use: `php -r "echo bin2hex(random_bytes(32));"`)
- [ ] Generate and set CACHE_ENCRYPTION_KEY
- [ ] Configure ALLOWED_ORIGINS for your domain
- [ ] Add pfSense credentials to .env
- [ ] Add AI provider API keys to .env
- [ ] Set .env permissions: `chmod 600 .env`
- [ ] Create log directory: `mkdir -p storage/logs`
- [ ] Set permissions: `chmod 700 storage/logs`
- [ ] Clear old cache: `rm -rf storage/cache/*`
- [ ] Test API with curl and API key
- [ ] Update frontend to send Authorization header
- [ ] Review logs for any errors
- [ ] Set APP_DEBUG=false before production

---

## 💡 Pro Tips

1. **Rotate API Keys Regularly**
   ```bash
   # Generate new key and test before switching
   php -r "echo bin2hex(random_bytes(32));"
   ```

2. **Monitor Failed Auth Attempts**
   ```bash
   grep "AUTH_FAILED\|Authentication error" storage/logs/pfsense-ai.log
   ```

3. **Cache Statistics**
   ```php
   $cache = SecureCache::getInstance();
   echo $cache->getStats()['size_mb'];  // Monitor cache growth
   ```

4. **Test Validators in CLI**
   ```bash
   php -r "
   require 'vendor/autoload.php';
   \$v = new \PfSenseAI\Utils\Validator();
   var_dump(\$v::validateFilter('test<script>'));
   "
   ```

5. **Generate Strong Keys**
   ```bash
   # API Key (hex, 64 chars = 32 bytes)
   php -r "echo 'API_KEY=' . bin2hex(random_bytes(32));"
   
   # Cache key (base64, ~44 chars = 32 bytes)
   php -r "echo 'CACHE_ENCRYPTION_KEY=' . base64_encode(openssl_random_pseudo_bytes(32));"
   ```

---

**Last Updated**: 2024-11-18  
**Security Level**: ⭐⭐⭐⭐⭐ Hardened
