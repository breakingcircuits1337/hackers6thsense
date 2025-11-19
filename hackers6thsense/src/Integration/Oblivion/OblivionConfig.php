<?php

namespace PfSenseAI\Integration\Oblivion;

use Psr\Log\LoggerInterface;

/**
 * OblivionConfig - Manages Oblivion integration configuration
 * Handles environment variables and settings for adversary emulation
 */
class OblivionConfig
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Check if Oblivion integration is enabled
     */
    public function isEnabled(): bool
    {
        return getenv('OBLIVION_ENABLED') === 'true' || getenv('OBLIVION_ENABLED') === '1';
    }

    /**
     * Get Mistral API key for attack planning
     */
    public function getMistralApiKey(): string
    {
        $key = getenv('MISTRAL_API_KEY');
        if (!$key) {
            throw new \Exception('MISTRAL_API_KEY environment variable not set');
        }
        return $key;
    }

    /**
     * Get Oblivion installation path
     */
    public function getOblivionPath(): string
    {
        return getenv('OBLIVION_PATH') ?: '/opt/oblivion';
    }

    /**
     * Get attack execution mode
     */
    public function getExecutionMode(): string
    {
        $mode = getenv('OBLIVION_EXECUTION_MODE') ?: 'simulation';
        return in_array($mode, ['simulation', 'training', 'assessment']) ? $mode : 'simulation';
    }

    /**
     * Check if authorized for real exploits
     */
    public function isAuthorizedForRealExploits(): bool
    {
        return getenv('OBLIVION_REAL_EXPLOITS_ENABLED') === 'true';
    }

    /**
     * Get metasploit endpoint
     */
    public function getMetasploitEndpoint(): string
    {
        return getenv('METASPLOIT_RPC_HOST') ?: 'localhost:55555';
    }

    /**
     * Get metasploit credentials
     */
    public function getMetasploitCredentials(): array
    {
        return [
            'username' => getenv('METASPLOIT_USER') ?: 'msf',
            'password' => getenv('METASPLOIT_PASS') ?: 'password'
        ];
    }

    /**
     * Check if phishing simulation is enabled
     */
    public function isPhishingSimulationEnabled(): bool
    {
        return getenv('OBLIVION_PHISHING_ENABLED') === 'true';
    }

    /**
     * Check if disinformation generation is enabled
     */
    public function isDisinformationEnabled(): bool
    {
        return getenv('OBLIVION_DISINFORMATION_ENABLED') === 'true';
    }

    /**
     * Get attack log level
     */
    public function getAttackLogLevel(): string
    {
        return getenv('OBLIVION_LOG_LEVEL') ?: 'INFO';
    }

    /**
     * Get maximum concurrent attacks
     */
    public function getMaxConcurrentAttacks(): int
    {
        $max = getenv('OBLIVION_MAX_CONCURRENT');
        return $max ? (int)$max : 5;
    }

    /**
     * Get attack timeout (seconds)
     */
    public function getAttackTimeout(): int
    {
        $timeout = getenv('OBLIVION_ATTACK_TIMEOUT');
        return $timeout ? (int)$timeout : 3600;
    }

    /**
     * Check if should alert security team on attacks
     */
    public function shouldAlertOnAttacks(): bool
    {
        return getenv('OBLIVION_ALERT_ON_ATTACK') === 'true';
    }

    /**
     * Get API endpoint for Oblivion server
     */
    public function getOblivionApiEndpoint(): string
    {
        return getenv('OBLIVION_API_ENDPOINT') ?: 'http://localhost:8000/api';
    }

    /**
     * Get all configuration as array
     */
    public function getAll(): array
    {
        return [
            'enabled' => $this->isEnabled(),
            'mistral_key_set' => !empty(getenv('MISTRAL_API_KEY')),
            'execution_mode' => $this->getExecutionMode(),
            'authorized_real_exploits' => $this->isAuthorizedForRealExploits(),
            'metasploit_endpoint' => $this->getMetasploitEndpoint(),
            'phishing_enabled' => $this->isPhishingSimulationEnabled(),
            'disinformation_enabled' => $this->isDisinformationEnabled(),
            'max_concurrent' => $this->getMaxConcurrentAttacks(),
            'attack_timeout' => $this->getAttackTimeout(),
            'alert_on_attack' => $this->shouldAlertOnAttacks()
        ];
    }
}
