<?php

namespace PfSenseAI\Utils;

/**
 * Database Configuration Manager
 * Supports SQLite, MySQL, PostgreSQL
 */
class DatabaseConfig
{
    private $config = [];

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $dbType = $_ENV['DB_TYPE'] ?? 'sqlite';
        
        switch ($dbType) {
            case 'mysql':
                $this->config = [
                    'type' => 'mysql',
                    'host' => $_ENV['DB_HOST'] ?? 'localhost',
                    'port' => $_ENV['DB_PORT'] ?? 3306,
                    'database' => $_ENV['DB_NAME'] ?? 'pfsense_ai',
                    'user' => $_ENV['DB_USER'] ?? 'root',
                    'password' => $_ENV['DB_PASSWORD'] ?? ''
                ];
                break;
                
            case 'pgsql':
                $this->config = [
                    'type' => 'pgsql',
                    'host' => $_ENV['DB_HOST'] ?? 'localhost',
                    'port' => $_ENV['DB_PORT'] ?? 5432,
                    'database' => $_ENV['DB_NAME'] ?? 'pfsense_ai',
                    'user' => $_ENV['DB_USER'] ?? 'postgres',
                    'password' => $_ENV['DB_PASSWORD'] ?? ''
                ];
                break;
                
            case 'sqlite':
            default:
                $this->config = [
                    'type' => 'sqlite',
                    'path' => $_ENV['DB_PATH'] ?? 'storage/pfsense-ai.db'
                ];
                break;
        }
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getDSN()
    {
        switch ($this->config['type']) {
            case 'mysql':
                return sprintf(
                    'mysql:host=%s;port=%d;dbname=%s',
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['database']
                );
                
            case 'pgsql':
                return sprintf(
                    'pgsql:host=%s;port=%d;dbname=%s',
                    $this->config['host'],
                    $this->config['port'],
                    $this->config['database']
                );
                
            case 'sqlite':
            default:
                return 'sqlite:' . $this->config['path'];
        }
    }

    public function getUsername()
    {
        return $this->config['user'] ?? null;
    }

    public function getPassword()
    {
        return $this->config['password'] ?? null;
    }
}
