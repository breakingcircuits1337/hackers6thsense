<?php
/**
 * Input Validator for sanitizing and validating API parameters
 */

namespace PfSenseAI\Utils;

class Validator
{
    private static $errors = [];

    /**
     * Validate timeframe parameter
     */
    public static function validateTimeframe(string $timeframe = null): string
    {
        $allowedTimeframes = ['last_hour', 'last_24_hours', 'last_7_days', 'last_30_days', 'custom'];
        
        if ($timeframe === null) {
            return 'last_hour';
        }

        if (!in_array($timeframe, $allowedTimeframes, true)) {
            self::addError('Invalid timeframe. Allowed values: ' . implode(', ', $allowedTimeframes));
            return 'last_hour';
        }

        return $timeframe;
    }

    /**
     * Validate integer parameter with min/max bounds
     */
    public static function validateInteger($value, int $min = 0, int $max = 1000, string $name = 'value'): ?int
    {
        if ($value === null) {
            return null;
        }

        $int = filter_var($value, FILTER_VALIDATE_INT);
        
        if ($int === false) {
            self::addError("$name must be an integer");
            return null;
        }

        if ($int < $min || $int > $max) {
            self::addError("$name must be between $min and $max");
            return null;
        }

        return $int;
    }

    /**
     * Validate filter string - limit length and allowed characters
     */
    public static function validateFilter(string $filter = null, int $maxLength = 500): ?string
    {
        if ($filter === null || $filter === '') {
            return null;
        }

        if (strlen($filter) > $maxLength) {
            self::addError("Filter exceeds maximum length of $maxLength characters");
            return null;
        }

        // Remove potentially dangerous characters but allow basic operators
        $sanitized = preg_replace('/[^\w\s\-\.@:\/\(\)\[\]\|\&\*\+\=]/u', '', $filter);
        
        return trim($sanitized);
    }

    /**
     * Validate search query - sanitize for log analysis
     */
    public static function validateQuery(string $query = null, int $maxLength = 1000): ?string
    {
        if ($query === null || $query === '') {
            return null;
        }

        if (strlen($query) > $maxLength) {
            self::addError("Query exceeds maximum length of $maxLength characters");
            return null;
        }

        // Allow alphanumeric, spaces, basic punctuation, and common operators
        $sanitized = preg_replace('/[^\w\s\.\,\-\:\"\'\&\|\(\)]/u', '', $query);
        
        return trim($sanitized);
    }

    /**
     * Validate limit parameter for pagination
     */
    public static function validateLimit($limit = null, int $maxLimit = 1000): int
    {
        $limitInt = self::validateInteger($limit, 1, $maxLimit, 'limit');
        return $limitInt ?? 100;
    }

    /**
     * Validate offset parameter for pagination
     */
    public static function validateOffset($offset = null): int
    {
        $offsetInt = self::validateInteger($offset, 0, PHP_INT_MAX, 'offset');
        return $offsetInt ?? 0;
    }

    /**
     * Validate IP address
     */
    public static function validateIp(string $ip = null): ?string
    {
        if ($ip === null) {
            return null;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            self::addError("Invalid IP address: $ip");
            return null;
        }

        return $ip;
    }

    /**
     * Validate port number
     */
    public static function validatePort($port = null): ?int
    {
        $portInt = self::validateInteger($port, 1, 65535, 'port');
        return $portInt;
    }

    /**
     * Validate analysis type
     */
    public static function validateAnalysisType(string $type = null): ?string
    {
        $allowedTypes = ['traffic', 'threat', 'config', 'log', 'anomaly'];
        
        if ($type === null) {
            return null;
        }

        if (!in_array($type, $allowedTypes, true)) {
            self::addError('Invalid analysis type. Allowed: ' . implode(', ', $allowedTypes));
            return null;
        }

        return $type;
    }

    /**
     * Sanitize string for output - prevent XSS
     */
    public static function sanitizeOutput(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Add validation error
     */
    public static function addError(string $message): void
    {
        self::$errors[] = $message;
    }

    /**
     * Get all validation errors
     */
    public static function getErrors(): array
    {
        return self::$errors;
    }

    /**
     * Check if there are validation errors
     */
    public static function hasErrors(): bool
    {
        return count(self::$errors) > 0;
    }

    /**
     * Clear validation errors
     */
    public static function clearErrors(): void
    {
        self::$errors = [];
    }
}
