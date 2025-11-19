<?php
/**
 * Secure Cache Utility with encryption
 * Encrypts cached data and uses versioned cache keys
 */

namespace PfSenseAI\Utils;

class SecureCache
{
    private static $instance;
    private $cacheDir;
    private $ttl = 3600; // 1 hour default
    private $encryptionKey;
    private $cacheVersion = 1;

    public function __construct()
    {
        $this->cacheDir = BASE_PATH . '/storage/cache';
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0700, true); // Restrictive permissions
        }

        // Generate or retrieve encryption key from environment
        $this->encryptionKey = $_ENV['CACHE_ENCRYPTION_KEY'] ?? null;
        
        if (empty($this->encryptionKey)) {
            // Fallback: use a derived key from API key if available
            $apiKey = $_ENV['API_KEY'] ?? null;
            if (!empty($apiKey)) {
                $this->encryptionKey = hash('sha256', $apiKey, true);
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set cache value with encryption
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        try {
            $ttl = $ttl ?? $this->ttl;
            
            $data = [
                'value' => $value,
                'expires' => time() + $ttl,
                'version' => $this->cacheVersion,
                'created' => time(),
            ];

            $file = $this->getFilePath($key);
            $jsonData = json_encode($data);

            // Encrypt data if encryption key is available
            if (!empty($this->encryptionKey)) {
                $jsonData = $this->encrypt($jsonData);
            }

            $written = file_put_contents($file, $jsonData, LOCK_EX);
            
            if ($written === false) {
                $logger = Logger::getInstance();
                $logger->error("Failed to write cache file: $file");
                return false;
            }

            // Set restrictive permissions
            chmod($file, 0600);
            return true;

        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->error("Cache set error: {error}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get cache value with decryption
     */
    public function get(string $key, $default = null)
    {
        try {
            $file = $this->getFilePath($key);

            if (!file_exists($file)) {
                return $default;
            }

            $jsonData = file_get_contents($file);

            if ($jsonData === false) {
                return $default;
            }

            // Decrypt data if encryption key is available
            if (!empty($this->encryptionKey)) {
                $jsonData = $this->decrypt($jsonData);
                if ($jsonData === null) {
                    // Decryption failed - cache may be corrupted
                    unlink($file);
                    return $default;
                }
            }

            $data = json_decode($jsonData, true);

            if (!is_array($data) || !isset($data['value'])) {
                return $default;
            }

            // Check if cache has expired
            if (isset($data['expires']) && $data['expires'] < time()) {
                unlink($file);
                return $default;
            }

            // Check version compatibility
            if (isset($data['version']) && $data['version'] !== $this->cacheVersion) {
                unlink($file);
                return $default;
            }

            return $data['value'];

        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->error("Cache get error: {error}", ['error' => $e->getMessage()]);
            return $default;
        }
    }

    /**
     * Check if cache key exists and is valid
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Delete cache entry
     */
    public function forget(string $key): bool
    {
        try {
            $file = $this->getFilePath($key);
            if (file_exists($file)) {
                return unlink($file);
            }
            return true;
        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->error("Cache forget error: {error}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Clear all cache entries
     */
    public function flush(): bool
    {
        try {
            $files = glob($this->cacheDir . '/v' . $this->cacheVersion . '_*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            return true;
        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->error("Cache flush error: {error}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Encrypt data using AES-256-GCM
     */
    private function encrypt(string $data): string
    {
        if (empty($this->encryptionKey)) {
            return $data;
        }

        try {
            $iv = openssl_random_pseudo_bytes(16);
            $encrypted = openssl_encrypt($data, 'AES-256-GCM', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
            
            if ($encrypted === false) {
                throw new \Exception('Encryption failed');
            }

            // Return base64 encoded IV + encrypted data
            return base64_encode($iv . $encrypted);

        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->error("Encryption error: {error}", ['error' => $e->getMessage()]);
            return $data; // Return unencrypted as fallback
        }
    }

    /**
     * Decrypt data using AES-256-GCM
     */
    private function decrypt(string $encoded): ?string
    {
        if (empty($this->encryptionKey)) {
            return $encoded;
        }

        try {
            $data = base64_decode($encoded, true);
            
            if ($data === false || strlen($data) < 16) {
                return null;
            }

            $iv = substr($data, 0, 16);
            $encrypted = substr($data, 16);

            $decrypted = openssl_decrypt($encrypted, 'AES-256-GCM', $this->encryptionKey, OPENSSL_RAW_DATA, $iv);
            
            if ($decrypted === false) {
                return null;
            }

            return $decrypted;

        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->error("Decryption error: {error}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Get versioned cache file path
     */
    private function getFilePath(string $key): string
    {
        // Use versioned cache keys with SHA256 for better distribution
        $hash = hash('sha256', $key);
        $filename = 'v' . $this->cacheVersion . '_' . $hash . '.cache';
        return $this->cacheDir . '/' . $filename;
    }

    /**
     * Rotate cache version (invalidates all cached data)
     */
    public function rotateVersion(): bool
    {
        $this->flush();
        $this->cacheVersion++;
        return true;
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $files = glob($this->cacheDir . '/v' . $this->cacheVersion . '_*');
            $count = count($files);
            $size = 0;

            foreach ($files as $file) {
                if (is_file($file)) {
                    $size += filesize($file);
                }
            }

            return [
                'version' => $this->cacheVersion,
                'entries' => $count,
                'size_bytes' => $size,
                'size_mb' => round($size / 1024 / 1024, 2),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
}
