<?php

namespace PfSenseAI\Agents;

use PfSenseAI\Utils\Logger;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Database\Database;
use PfSenseAI\Integration\LEGION\LegionBridge;
use PfSenseAI\Integration\LEGION\LegionConfig;
use PfSenseAI\Integration\LEGION\ThreatHandler;

/**
 * Agent Scheduler
 * Manages recurring agent execution with multiple frequencies
 */
class AgentScheduler
{
    private $logger;
    private $validator;
    private $db;
    private $orchestrator;
    private $legionBridge;
    private $legionConfig;
    private $threatHandler;

    public function __construct()
    {
        $this->logger = new Logger('scheduler');
        $this->validator = new Validator();
        $this->db = Database::getInstance();
        $this->orchestrator = new AgentOrchestrator();
        
        // Initialize LEGION components if enabled
        $this->legionConfig = new LegionConfig();
        if ($this->legionConfig->isEnabled()) {
            $this->legionBridge = new LegionBridge($this->db, $this->logger);
            $this->threatHandler = new ThreatHandler($this->db, $this->logger, $this->legionBridge, $this->legionConfig);
        }
    }

    /**
     * Create a new schedule
     */
    public function createSchedule($agentId, $frequency = 'daily', $config = [])
    {
        // Validate inputs
        if (!$this->validator->validateString($agentId, 1, 100)) {
            throw new \Exception('Invalid agent ID');
        }

        $validFrequencies = ['hourly', 'daily', 'weekly', 'monthly', 'every_4_hours', 'every_30_minutes'];
        if (!in_array($frequency, $validFrequencies)) {
            throw new \Exception('Invalid frequency: ' . $frequency);
        }

        $scheduleId = $this->db->insert('schedules', [
            'agent_id' => $agentId,
            'frequency' => $frequency,
            'config' => json_encode($config),
            'is_active' => 1,
            'last_execution' => null,
            'next_execution' => $this->calculateNextExecution($frequency),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->logger->info("Schedule created: $scheduleId for agent $agentId");
        return $scheduleId;
    }

    /**
     * Get all schedules
     */
    public function getSchedules($isActive = null)
    {
        $where = [];
        if ($isActive !== null) {
            $where['is_active'] = $isActive ? 1 : 0;
        }
        return $this->db->find('schedules', $where);
    }

    /**
     * Get specific schedule
     */
    public function getSchedule($scheduleId)
    {
        return $this->db->findById('schedules', $scheduleId);
    }

    /**
     * Update schedule
     */
    public function updateSchedule($scheduleId, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->update('schedules', $data, ['id' => $scheduleId]);
        
        $this->logger->info("Schedule updated: $scheduleId");
        return true;
    }

    /**
     * Delete schedule
     */
    public function deleteSchedule($scheduleId)
    {
        $this->db->delete('schedules', ['id' => $scheduleId]);
        $this->logger->info("Schedule deleted: $scheduleId");
        return true;
    }

    /**
     * Execute scheduled jobs
     */
    public function executeScheduledJobs()
    {
        $now = new \DateTime();
        $schedules = $this->db->find('schedules', ['is_active' => 1]);
        
        $executed = [];

        foreach ($schedules as $schedule) {
            $nextExecution = new \DateTime($schedule['next_execution']);
            
            if ($now >= $nextExecution) {
                $result = $this->executeSchedule($schedule);
                $executed[] = [
                    'schedule_id' => $schedule['id'],
                    'agent_id' => $schedule['agent_id'],
                    'result' => $result
                ];
            }
        }

        $this->logger->info("Executed " . count($executed) . " scheduled jobs");
        return ['executed' => count($executed), 'jobs' => $executed];
    }

    /**
     * Execute individual schedule
     */
    private function executeSchedule($schedule)
    {
        try {
            $config = json_decode($schedule['config'], true) ?? [];
            
            // Execute the agent
            $result = $this->orchestrator->executeAgent($schedule['agent_id'], $config);

            // LEGION Integration: Handle threat analysis for scheduled executions
            if ($this->legionConfig->isEnabled() && isset($result['execution_id'])) {
                try {
                    // Start LEGION defender session before threat analysis
                    $defenderSession = $this->legionBridge->startDefenderSession([
                        'agent_id' => $schedule['agent_id'],
                        'schedule_id' => $schedule['id'],
                        'execution_type' => 'scheduled'
                    ]);

                    // Extract threat indicators from agent result
                    if (isset($result['result']) && is_array($result['result'])) {
                        $threatLevel = $result['result']['threat_level'] ?? 1;
                        
                        if ($threatLevel >= $this->legionConfig->getThreatThreshold()) {
                            // Handle threat with escalation
                            $threatAnalysis = $this->threatHandler->handleThreat([
                                'type' => $result['result']['type'] ?? 'unknown',
                                'threat_level' => $threatLevel,
                                'confidence' => $result['result']['confidence'] ?? 0.5,
                                'analysis' => $result['result']['analysis'] ?? 'Scheduled agent threat analysis',
                                'recommendations' => $result['result']['recommendations'] ?? []
                            ], $result['execution_id'], $schedule['agent_id']);

                            $this->logger->info("Threat handled for scheduled execution: " . json_encode($threatAnalysis));
                        }
                    }
                } catch (\Exception $e) {
                    $this->logger->warning("LEGION threat analysis failed for schedule: " . $e->getMessage());
                    // Don't fail the schedule execution if LEGION fails
                }
            }

            // Update schedule with execution info
            $this->db->update('schedules', [
                'last_execution' => date('Y-m-d H:i:s'),
                'next_execution' => $this->calculateNextExecution($schedule['frequency'])
            ], ['id' => $schedule['id']]);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Schedule execution failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Calculate next execution time
     */
    private function calculateNextExecution($frequency)
    {
        $now = new \DateTime();

        switch ($frequency) {
            case 'every_30_minutes':
                $now->add(new \DateInterval('PT30M'));
                break;
            case 'every_4_hours':
                $now->add(new \DateInterval('PT4H'));
                break;
            case 'hourly':
                $now->add(new \DateInterval('PT1H'));
                break;
            case 'daily':
                $now->add(new \DateInterval('P1D'));
                break;
            case 'weekly':
                $now->add(new \DateInterval('P7D'));
                break;
            case 'monthly':
                $now->add(new \DateInterval('P1M'));
                break;
            default:
                $now->add(new \DateInterval('P1D'));
        }

        return $now->format('Y-m-d H:i:s');
    }

    /**
     * Get execution history
     */
    public function getExecutionHistory($filters = [], $limit = 100, $offset = 0)
    {
        $history = $this->db->find('execution_history', $filters, $limit, $offset);
        
        return [
            'total' => $this->db->count('execution_history', $filters),
            'limit' => $limit,
            'offset' => $offset,
            'history' => $history
        ];
    }

    /**
     * Get execution statistics
     */
    public function getStatistics()
    {
        $totalSchedules = $this->db->count('schedules');
        $activeSchedules = $this->db->count('schedules', ['is_active' => 1]);
        $totalExecutions = $this->db->count('execution_history');
        $successfulExecutions = $this->db->count('execution_history', ['status' => 'completed']);
        $failedExecutions = $this->db->count('execution_history', ['status' => 'error']);

        return [
            'total_schedules' => $totalSchedules,
            'active_schedules' => $activeSchedules,
            'total_executions' => $totalExecutions,
            'successful_executions' => $successfulExecutions,
            'failed_executions' => $failedExecutions,
            'success_rate' => $totalExecutions > 0 ? ($successfulExecutions / $totalExecutions) * 100 : 0
        ];
    }

    /**
     * Disable schedule
     */
    public function disableSchedule($scheduleId)
    {
        $this->db->update('schedules', [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $scheduleId]);

        $this->logger->info("Schedule disabled: $scheduleId");
        return true;
    }

    /**
     * Enable schedule
     */
    public function enableSchedule($scheduleId)
    {
        $this->db->update('schedules', [
            'is_active' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $scheduleId]);

        $this->logger->info("Schedule enabled: $scheduleId");
        return true;
    }
}
