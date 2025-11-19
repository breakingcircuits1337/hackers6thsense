<?php
/**
 * Application Bootstrap
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('SRC_PATH', BASE_PATH . '/src');

// Autoloading
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = new \Dotenv\Dotenv(BASE_PATH);
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv->load();
} elseif (file_exists(BASE_PATH . '/.env.example')) {
    throw new Exception('Please copy .env.example to .env and configure your settings');
}

// Initialize logger and error handler
use PfSenseAI\Utils\Logger;
use PfSenseAI\Utils\ErrorHandler;
use PfSenseAI\Auth\AuthMiddleware;

$logger = Logger::getInstance();
$errorHandler = new ErrorHandler();

// Set error handlers with proper logging
set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logger, $errorHandler) {
    $logger->error("PHP Error [{errno}]: {message} in {file}:{line}", [
        'errno' => $errno,
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline,
    ]);
    return false;
});

set_exception_handler(function($exception) use ($errorHandler) {
    $errorHandler->handleException($exception, 'Uncaught Exception');
});

// Load configuration
$config = new \PfSenseAI\Utils\Config();

// Initialize database and run migrations
use PfSenseAI\Database\Database;
use PfSenseAI\Database\Migration;

try {
    $db = Database::getInstance();
    
    // Ensure all tables exist
    $migration = new Migration($_ENV['DB_PATH'] ?? 'storage/pfsense-ai.db');
    $migration->migrate();
    
    $logger->info("Database initialized successfully");
} catch (\Exception $e) {
    $logger->error("Database initialization failed: " . $e->getMessage());
    // Continue - database may be optional for some endpoints
}

// Set security headers
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Content-Security-Policy: default-src \'self\'');

// Initialize authentication and CORS
$auth = new AuthMiddleware();

// Apply CORS headers for allowed origins
$auth->applyCorsHeaders();

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Validate CORS origin
if (!$auth->validateCors()) {
    $errorHandler->handleAuthorizationError('CORS validation failed');
}

// Authenticate request (except for public endpoints)
$publicEndpoints = ['/api/system/status', '/api/system/providers'];
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!in_array($currentPath, $publicEndpoints, true)) {
    if (!$auth->authenticate()) {
        $errorHandler->handleAuthError('Authentication required');
    }
}

define('BOOTSTRAP_LOADED', true);
