<?php
/**
 * Authentication Middleware for API requests
 */

namespace PfSenseAI\Auth;

use PfSenseAI\Utils\Logger;

class AuthMiddleware
{
    private $logger;
    private $apiKey;
    private $allowedOrigins = [];

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->apiKey = $_ENV['API_KEY'] ?? null;
        
        // Configure allowed origins from environment
        $allowedOriginsEnv = $_ENV['ALLOWED_ORIGINS'] ?? 'http://localhost:3000';
        $this->allowedOrigins = array_map('trim', explode(',', $allowedOriginsEnv));
    }

    /**
     * Validate incoming request authentication
     */
    public function authenticate(): bool
    {
        // Allow preflight OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            return true;
        }

        // Check if API key is configured
        if (empty($this->apiKey)) {
            $this->logger->warning('API key not configured in environment');
            return false;
        }

        // Get authorization header
        $authHeader = $this->getAuthorizationHeader();

        if (empty($authHeader)) {
            $this->logger->warning('Missing authorization header');
            return false;
        }

        // Validate Bearer token
        if (strpos($authHeader, 'Bearer ') === 0) {
            $token = substr($authHeader, 7);
            return $this->validateToken($token);
        }

        $this->logger->warning('Invalid authorization header format');
        return false;
    }

    /**
     * Validate CORS request
     */
    public function validateCors(): bool
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (empty($origin)) {
            return true; // Non-CORS request
        }

        if (!in_array($origin, $this->allowedOrigins, true)) {
            $this->logger->warning("CORS request from unauthorized origin: $origin");
            return false;
        }

        return true;
    }

    /**
     * Apply CORS headers for allowed origin
     */
    public function applyCorsHeaders(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        if (!empty($origin) && in_array($origin, $this->allowedOrigins, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            header('Access-Control-Max-Age: 86400');
            header('Vary: Origin');
        }
    }

    /**
     * Get Authorization header
     */
    private function getAuthorizationHeader(): ?string
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return null;
    }

    /**
     * Validate API token
     */
    private function validateToken(string $token): bool
    {
        // Simple token validation - compare with configured API key
        // In production, use JWT or OAuth2
        if (hash_equals($this->apiKey, $token)) {
            return true;
        }

        $this->logger->warning('Invalid API token provided');
        return false;
    }

    /**
     * Get current user/client info from token (for future JWT implementation)
     */
    public function getClientInfo(): ?array
    {
        // Placeholder for future JWT decoding
        return [
            'id' => 'client',
            'role' => 'user',
            'permissions' => ['read', 'analyze'],
        ];
    }
}
