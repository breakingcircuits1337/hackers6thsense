<?php

namespace PfSenseAI\Database;

use PfSenseAI\Utils\Logger;

/**
 * Database Migration Handler
 * Creates all necessary tables and schema
 */
class Migration
{
    private $db;
    private $logger;

    public function __construct($dbPath = 'storage/pfsense-ai.db')
    {
        $this->logger = new Logger('migration');
        $this->initializeDatabase($dbPath);
    }

    private function initializeDatabase($dbPath)
    {
        try {
            $this->db = new \PDO('sqlite:' . $dbPath);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->logger->info("Connected to database: {$dbPath}");
        } catch (\Exception $e) {
            $this->logger->error("Database connection failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run all migrations
     */
    public function migrate()
    {
        $this->createAgentsTable();
        $this->createSchedulesTable();
        $this->createExecutionHistoryTable();
        $this->createAgentResultsTable();
        $this->createFiltersTable();
        $this->createLegionAnalysisTable();
        $this->createLegionCorrelationsTable();
        $this->createOblivionSessionsTable();
        $this->createOblivionAttacksTable();
        $this->createOblivionAttackPlansTable();
        $this->createOblivionPhishingTable();
        $this->createOblivionDisinformationTable();
        $this->logger->info("All migrations completed successfully");
    }

    /**
     * Create agents table
     */
    private function createAgentsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS agents (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                agent_id INTEGER NOT NULL UNIQUE,
                name TEXT NOT NULL,
                category TEXT NOT NULL,
                status TEXT DEFAULT 'idle',
                description TEXT,
                config JSON,
                success_rate REAL DEFAULT 0,
                total_runs INTEGER DEFAULT 0,
                successful_runs INTEGER DEFAULT 0,
                failed_runs INTEGER DEFAULT 0,
                last_run DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created agents table");
    }

    /**
     * Create schedules table
     */
    private function createSchedulesTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS schedules (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                agent_id INTEGER NOT NULL,
                schedule_name TEXT NOT NULL,
                frequency TEXT NOT NULL,
                cron_expression TEXT,
                targets JSON NOT NULL,
                parameters JSON,
                enabled BOOLEAN DEFAULT 1,
                last_executed DATETIME,
                next_execution DATETIME,
                execution_count INTEGER DEFAULT 0,
                success_count INTEGER DEFAULT 0,
                failure_count INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (agent_id) REFERENCES agents(agent_id)
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created schedules table");
    }

    /**
     * Create execution history table
     */
    private function createExecutionHistoryTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS execution_history (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                schedule_id INTEGER,
                agent_id INTEGER NOT NULL,
                target TEXT,
                status TEXT NOT NULL,
                results JSON,
                error_message TEXT,
                execution_time_ms INTEGER,
                started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                completed_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (schedule_id) REFERENCES schedules(id),
                FOREIGN KEY (agent_id) REFERENCES agents(agent_id)
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created execution_history table");
    }

    /**
     * Create agent results table
     */
    private function createAgentResultsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS agent_results (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                execution_id INTEGER NOT NULL,
                agent_id INTEGER NOT NULL,
                target TEXT NOT NULL,
                result_type TEXT,
                result_data JSON,
                severity TEXT,
                recommendations TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (execution_id) REFERENCES execution_history(id),
                FOREIGN KEY (agent_id) REFERENCES agents(agent_id)
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created agent_results table");
    }

    /**
     * Create filters table
     */
    private function createFiltersTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS filters (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                filter_name TEXT NOT NULL UNIQUE,
                filter_type TEXT NOT NULL,
                criteria JSON NOT NULL,
                description TEXT,
                is_public BOOLEAN DEFAULT 0,
                owner_id TEXT,
                usage_count INTEGER DEFAULT 0,
                last_used DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created filters table");
    }

    /**
     * Create LEGION analysis table
     */
    private function createLegionAnalysisTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS legion_analysis (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT,
                threat_data JSON NOT NULL,
                analysis JSON NOT NULL,
                threat_level INTEGER,
                recommendations JSON,
                confidence REAL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created legion_analysis table");
    }

    /**
     * Create LEGION correlations table
     */
    private function createLegionCorrelationsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS legion_correlations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                agent_id TEXT NOT NULL,
                execution_id INTEGER NOT NULL,
                correlation JSON NOT NULL,
                correlation_score REAL,
                threat_intel JSON,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (execution_id) REFERENCES execution_history(id)
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created legion_correlations table");
    }

    /**
     * Create Oblivion sessions table
     */
    private function createOblivionSessionsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS oblivion_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL UNIQUE,
                agent_id INTEGER NOT NULL,
                agent_type TEXT NOT NULL,
                target_params JSON,
                status TEXT DEFAULT 'initialized',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (agent_id) REFERENCES agents(id)
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created oblivion_sessions table");
    }

    /**
     * Create Oblivion attacks table
     */
    private function createOblivionAttacksTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS oblivion_attacks (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                attack_id TEXT NOT NULL UNIQUE,
                attack_type TEXT NOT NULL,
                target TEXT NOT NULL,
                parameters JSON,
                status TEXT DEFAULT 'initiated',
                results JSON,
                started_at DATETIME,
                completed_at DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created oblivion_attacks table");
    }

    /**
     * Create Oblivion attack plans table
     */
    private function createOblivionAttackPlansTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS oblivion_attack_plans (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                goal TEXT NOT NULL,
                constraints JSON,
                plan TEXT NOT NULL,
                model TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created oblivion_attack_plans table");
    }

    /**
     * Create Oblivion phishing table
     */
    private function createOblivionPhishingTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS oblivion_phishing (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                target_organization TEXT NOT NULL,
                pretext TEXT,
                email_content TEXT NOT NULL,
                success_rate REAL,
                attempts INTEGER DEFAULT 0,
                successes INTEGER DEFAULT 0,
                generated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created oblivion_phishing table");
    }

    /**
     * Create Oblivion disinformation table
     */
    private function createOblivionDisinformationTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS oblivion_disinformation (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                topic TEXT NOT NULL,
                context JSON,
                content TEXT NOT NULL,
                views INTEGER DEFAULT 0,
                engagements INTEGER DEFAULT 0,
                generated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ";
        $this->db->exec($sql);
        $this->logger->info("Created oblivion_disinformation table");
    }

    public function getConnection()
    {
        return $this->db;
    }
}
