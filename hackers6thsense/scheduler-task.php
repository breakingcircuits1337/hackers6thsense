<?php

/**
 * Scheduler Task Runner
 * Execute scheduled jobs via cron/Windows Task Scheduler
 * 
 * Usage (Linux/Mac):
 *   * * * * * php /path/to/scheduler-task.php >> /var/log/pfsense-scheduler.log 2>&1
 * 
 * Usage (Windows Task Scheduler):
 *   C:\php.exe C:\path\to\scheduler-task.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

define('APP_ROOT', __DIR__);

// Load environment
if (file_exists(APP_ROOT . '/.env')) {
    $dotenv = new \Dotenv\Dotenv(APP_ROOT);
    $dotenv->load();
}

// Load autoloader
require_once APP_ROOT . '/vendor/autoload.php';

use PfSenseAI\Database\Database;
use PfSenseAI\Agents\AgentScheduler;
use PfSenseAI\Utils\Logger;

try {
    $logger = new Logger('scheduler-task');
    
    // Initialize database
    Database::getInstance();
    
    // Create scheduler
    $scheduler = new AgentScheduler();
    
    // Execute all due scheduled jobs
    $logger->info("Scheduler task started");
    $result = $scheduler->executeScheduledJobs();
    $logger->info("Scheduler task completed - executed: " . $result['executed'] . " jobs");
    
    exit(0);
} catch (\Exception $e) {
    $logger = $logger ?? new Logger('scheduler-task');
    $logger->error("Scheduler task failed: " . $e->getMessage());
    exit(1);
}
