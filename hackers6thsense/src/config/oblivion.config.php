<?php
/**
 * Oblivion Integration Configuration
 * Manages integration settings for the Oblivion cyber range platform
 */

return [
    // Oblivion Service Configuration
    'service' => [
        'enabled' => getenv('OBLIVION_ENABLED') ?? true,
        'base_url' => getenv('OBLIVION_BASE_URL') ?? 'http://localhost:8080',
        'api_version' => 'v1',
        'timeout' => 30,
        'verify_ssl' => getenv('OBLIVION_VERIFY_SSL') ?? true,
    ],

    // Authentication
    'auth' => [
        'type' => getenv('OBLIVION_AUTH_TYPE') ?? 'bearer',
        'token' => getenv('OBLIVION_AUTH_TOKEN') ?? null,
        'api_key' => getenv('OBLIVION_API_KEY') ?? null,
    ],

    // Scenario Configuration
    'scenarios' => [
        'auto_deploy' => getenv('OBLIVION_AUTO_DEPLOY') ?? false,
        'max_concurrent' => getenv('OBLIVION_MAX_CONCURRENT') ?? 5,
        'default_duration' => getenv('OBLIVION_DEFAULT_DURATION') ?? 3600,
        'cleanup_on_stop' => getenv('OBLIVION_CLEANUP_ON_STOP') ?? true,
    ],

    // Attack Modules
    'attacks' => [
        'enabled_modules' => explode(',', getenv('OBLIVION_ENABLED_MODULES') ?? 'reconnaissance,delivery,exploitation,exfiltration'),
        'max_parallel_attacks' => getenv('OBLIVION_MAX_PARALLEL') ?? 3,
        'log_level' => getenv('OBLIVION_LOG_LEVEL') ?? 'INFO',
    ],

    // Assets Management
    'assets' => [
        'auto_discovery' => getenv('OBLIVION_AUTO_DISCOVERY') ?? true,
        'sync_interval' => getenv('OBLIVION_SYNC_INTERVAL') ?? 300,
        'store_type' => getenv('OBLIVION_ASSET_STORE') ?? 'local', // local, redis, database
    ],

    // Policy Management
    'policy' => [
        'validation_enabled' => getenv('OBLIVION_POLICY_VALIDATION') ?? true,
        'policy_file' => getenv('OBLIVION_POLICY_FILE') ?? '../../../Oblivion-main/policy.yaml',
        'strict_mode' => getenv('OBLIVION_STRICT_MODE') ?? false,
    ],

    // Logging & Monitoring
    'logging' => [
        'enabled' => getenv('OBLIVION_LOGGING_ENABLED') ?? true,
        'log_events' => getenv('OBLIVION_LOG_EVENTS') ?? true,
        'log_attacks' => getenv('OBLIVION_LOG_ATTACKS') ?? true,
        'log_scenarios' => getenv('OBLIVION_LOG_SCENARIOS') ?? true,
        'retention_days' => getenv('OBLIVION_LOG_RETENTION') ?? 30,
    ],

    // Webhook Configuration
    'webhooks' => [
        'enabled' => getenv('OBLIVION_WEBHOOKS_ENABLED') ?? true,
        'scenario_start' => getenv('OBLIVION_WEBHOOK_SCENARIO_START') ?? '/api/webhooks/oblivion/scenario/start',
        'scenario_stop' => getenv('OBLIVION_WEBHOOK_SCENARIO_STOP') ?? '/api/webhooks/oblivion/scenario/stop',
        'attack_executed' => getenv('OBLIVION_WEBHOOK_ATTACK_EXECUTED') ?? '/api/webhooks/oblivion/attack/executed',
        'event_generated' => getenv('OBLIVION_WEBHOOK_EVENT_GENERATED') ?? '/api/webhooks/oblivion/event/generated',
    ],

    // Integration Settings
    'integration' => [
        'sync_with_threat_intel' => getenv('OBLIVION_SYNC_THREAT_INTEL') ?? true,
        'send_alerts_to_chat' => getenv('OBLIVION_SEND_ALERTS_CHAT') ?? true,
        'store_results' => getenv('OBLIVION_STORE_RESULTS') ?? true,
        'export_to_legion' => getenv('OBLIVION_EXPORT_LEGION') ?? true,
    ],

    // Performance Tuning
    'performance' => [
        'cache_scenarios' => getenv('OBLIVION_CACHE_SCENARIOS') ?? true,
        'cache_ttl' => getenv('OBLIVION_CACHE_TTL') ?? 3600,
        'batch_size' => getenv('OBLIVION_BATCH_SIZE') ?? 100,
        'max_connections' => getenv('OBLIVION_MAX_CONNECTIONS') ?? 50,
    ],
];
