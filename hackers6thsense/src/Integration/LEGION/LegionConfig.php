<?php

namespace PfSenseAI\Integration\LEGION;

/**
 * LEGION Configuration Manager
 */
class LegionConfig
{
    private $config = [];

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $this->config = [
            'enabled' => $_ENV['LEGION_ENABLED'] === 'true',
            'endpoint' => $_ENV['LEGION_ENDPOINT'] ?? 'http://localhost:3000',
            'api_key' => $_ENV['LEGION_API_KEY'] ?? null,
            'providers' => explode(',', $_ENV['LEGION_PROVIDERS'] ?? 'groq,gemini,mistral'),
            'default_threat_level' => (int)($_ENV['LEGION_DEFAULT_THREAT_LEVEL'] ?? 2),
            'auto_correlate' => $_ENV['LEGION_AUTO_CORRELATE'] === 'true',
            'correlation_threshold' => (float)($_ENV['LEGION_CORRELATION_THRESHOLD'] ?? 0.7),
            'alert_on_threat' => $_ENV['LEGION_ALERT_ON_THREAT'] === 'true',
            'threat_threshold' => (int)($_ENV['LEGION_THREAT_THRESHOLD'] ?? 3),
            'integration_mode' => $_ENV['LEGION_INTEGRATION_MODE'] ?? 'passive',
            'cache_ttl' => (int)($_ENV['LEGION_CACHE_TTL'] ?? 3600),
        ];
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function isEnabled()
    {
        return $this->config['enabled'];
    }

    public function getEndpoint()
    {
        return $this->config['endpoint'];
    }

    public function getApiKey()
    {
        return $this->config['api_key'];
    }

    public function getProviders()
    {
        return $this->config['providers'];
    }

    public function getDefaultThreatLevel()
    {
        return $this->config['default_threat_level'];
    }

    public function shouldAutoCorrelate()
    {
        return $this->config['auto_correlate'];
    }

    public function getCorrelationThreshold()
    {
        return $this->config['correlation_threshold'];
    }

    public function shouldAlertOnThreat()
    {
        return $this->config['alert_on_threat'];
    }

    public function getThreatThreshold()
    {
        return $this->config['threat_threshold'];
    }

    public function getIntegrationMode()
    {
        return $this->config['integration_mode'];
    }

    public function getCacheTTL()
    {
        return $this->config['cache_ttl'];
    }
}
