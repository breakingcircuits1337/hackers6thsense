<?php
/**
 * Error Handler for standardized API error responses
 */

namespace PfSenseAI\Utils;

class ErrorHandler
{
    private $logger;
    private $debug = false;

    // Standardized error codes
    const AUTH_FAILED = 'AUTH_FAILED';
    const AUTH_REQUIRED = 'AUTH_REQUIRED';
    const FORBIDDEN = 'FORBIDDEN';
    const NOT_FOUND = 'NOT_FOUND';
    const VALIDATION_ERROR = 'VALIDATION_ERROR';
    const INTERNAL_ERROR = 'INTERNAL_ERROR';
    const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';

    // HTTP status codes
    const STATUS_OK = 200;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_UNAUTHORIZED = 401;
    const STATUS_FORBIDDEN = 403;
    const STATUS_NOT_FOUND = 404;
    const STATUS_INTERNAL_ERROR = 500;
    const STATUS_SERVICE_UNAVAILABLE = 503;

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $config = Config::getInstance();
        $this->debug = $config->get('app.debug', false);
    }

    /**
     * Handle validation errors
     */
    public function handleValidationError(array $errors): void
    {
        $this->respond([
            'error_code' => self::VALIDATION_ERROR,
            'message' => 'Validation failed',
            'details' => $errors,
        ], self::STATUS_BAD_REQUEST);

        $this->logger->warning('Validation error: ' . implode(', ', $errors));
    }

    /**
     * Handle authentication errors
     */
    public function handleAuthError(string $message = 'Authentication failed'): void
    {
        $this->respond([
            'error_code' => self::AUTH_FAILED,
            'message' => $message,
        ], self::STATUS_UNAUTHORIZED);

        $this->logger->warning('Authentication error: ' . $message);
    }

    /**
     * Handle authorization errors
     */
    public function handleAuthorizationError(string $message = 'Access denied'): void
    {
        $this->respond([
            'error_code' => self::FORBIDDEN,
            'message' => $message,
        ], self::STATUS_FORBIDDEN);

        $this->logger->warning('Authorization error: ' . $message);
    }

    /**
     * Handle not found errors
     */
    public function handleNotFound(string $message = 'Endpoint not found'): void
    {
        $this->respond([
            'error_code' => self::NOT_FOUND,
            'message' => $message,
        ], self::STATUS_NOT_FOUND);

        $this->logger->info('Not found: ' . $message);
    }

    /**
     * Handle service unavailable errors
     */
    public function handleServiceUnavailable(string $message = 'Service temporarily unavailable'): void
    {
        $this->respond([
            'error_code' => self::SERVICE_UNAVAILABLE,
            'message' => $message,
        ], self::STATUS_SERVICE_UNAVAILABLE);

        $this->logger->error('Service unavailable: ' . $message);
    }

    /**
     * Handle generic exceptions
     */
    public function handleException(\Exception $exception, string $context = ''): void
    {
        // Log full exception details server-side
        $this->logger->error(
            'Exception in {context}: {message}',
            [
                'context' => $context,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]
        );

        // Return generic error to client (don't expose stack traces)
        $response = [
            'error_code' => self::INTERNAL_ERROR,
            'message' => 'An error occurred while processing your request',
        ];

        // In debug mode, include more details
        if ($this->debug) {
            $response['details'] = [
                'message' => $exception->getMessage(),
                'file' => basename($exception->getFile()),
                'line' => $exception->getLine(),
            ];
        }

        $this->respond($response, self::STATUS_INTERNAL_ERROR);
    }

    /**
     * Send standardized response
     */
    public function respond(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send success response
     */
    public function success(array $data, int $statusCode = 200): void
    {
        $response = array_merge(['success' => true], $data);
        $this->respond($response, $statusCode);
    }

    /**
     * Convert old exception messages to standardized format
     */
    public static function sanitizeErrorMessage(string $message): string
    {
        // Remove file paths and sensitive details
        $message = preg_replace('/\/[a-z\/]+\.php:\d+/', '[internal]', $message);
        $message = preg_replace('/\[.*?\]/', '', $message); // Remove brackets with details
        return trim($message);
    }
}
