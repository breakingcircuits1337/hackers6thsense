<?php

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\Agents\AgentOrchestrator;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Utils\Logger;
use PfSenseAI\Utils\ErrorHandler;

/**
 * Agent Endpoint
 * REST API for agent management and execution
 */
class AgentEndpoint
{
    private $orchestrator;
    private $validator;
    private $logger;
    private $errorHandler;

    public function __construct()
    {
        $this->orchestrator = new AgentOrchestrator();
        $this->validator = new Validator();
        $this->logger = new Logger('agent-endpoint');
        $this->errorHandler = new ErrorHandler();
    }

    /**
     * GET /api/agents
     * List all agents
     */
    public function listAgents()
    {
        try {
            $category = $_GET['category'] ?? null;
            $agents = $this->orchestrator->getAgents($category);

            return [
                'status' => 'success',
                'data' => $agents,
                'count' => count($agents)
            ];
        } catch (\Exception $e) {
            $this->logger->error("List agents failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to list agents', $e->getMessage());
        }
    }

    /**
     * GET /api/agents/{id}
     * Get specific agent
     */
    public function getAgent($agentId)
    {
        try {
            if (!$this->validator->validateString($agentId, 1, 100)) {
                return $this->errorHandler->error('Invalid agent ID');
            }

            $agent = $this->orchestrator->getAgent($agentId);
            
            if (!$agent) {
                return $this->errorHandler->error('Agent not found', '', 404);
            }

            return [
                'status' => 'success',
                'data' => $agent
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get agent failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get agent', $e->getMessage());
        }
    }

    /**
     * POST /api/agents/{id}/execute
     * Execute single agent
     */
    public function executeAgent($agentId)
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$this->validator->validateString($agentId, 1, 100)) {
                return $this->errorHandler->error('Invalid agent ID');
            }

            $config = $input['config'] ?? [];
            $result = $this->orchestrator->executeAgent($agentId, $config);

            if ($result['status'] === 'error') {
                return $this->errorHandler->error('Agent execution failed', $result['message']);
            }

            $this->logger->info("Agent executed: $agentId (Execution ID: {$result['execution_id']})");

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Execute agent failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to execute agent', $e->getMessage());
        }
    }

    /**
     * POST /api/agents/batch/execute
     * Execute multiple agents in parallel
     */
    public function executeBatch()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $agentIds = $input['agent_ids'] ?? [];
            if (empty($agentIds) || !is_array($agentIds)) {
                return $this->errorHandler->error('Invalid or missing agent_ids array');
            }

            if (count($agentIds) > 50) {
                return $this->errorHandler->error('Maximum 50 agents allowed per batch');
            }

            // Validate each agent ID
            foreach ($agentIds as $agentId) {
                if (!$this->validator->validateString($agentId, 1, 100)) {
                    return $this->errorHandler->error('Invalid agent ID: ' . $agentId);
                }
            }

            $config = $input['config'] ?? [];
            $result = $this->orchestrator->executeAgentsParallel($agentIds, $config);

            $this->logger->info("Batch execution started: " . count($agentIds) . " agents");

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Batch execution failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to execute batch', $e->getMessage());
        }
    }

    /**
     * GET /api/agents/{id}/results
     * Get agent execution results
     */
    public function getResults($agentId)
    {
        try {
            if (!$this->validator->validateString($agentId, 1, 100)) {
                return $this->errorHandler->error('Invalid agent ID');
            }

            $limit = min((int)($_GET['limit'] ?? 20), 100);
            $offset = max((int)($_GET['offset'] ?? 0), 0);

            // This would fetch from database in production
            $results = [
                'status' => 'success',
                'data' => [
                    'agent_id' => $agentId,
                    'total_executions' => 0,
                    'results' => []
                ]
            ];

            return $results;
        } catch (\Exception $e) {
            $this->logger->error("Get results failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get results', $e->getMessage());
        }
    }

    /**
     * GET /api/agents/active
     * Get currently running agents
     */
    public function getActiveAgents()
    {
        try {
            // In production, fetch from execution_history where status = 'running'
            return [
                'status' => 'success',
                'data' => [
                    'active_agents' => 0,
                    'agents' => []
                ]
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get active agents failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get active agents', $e->getMessage());
        }
    }

    /**
     * POST /api/agents/{id}/stop
     * Stop running agent
     */
    public function stopAgent($agentId)
    {
        try {
            if (!$this->validator->validateString($agentId, 1, 100)) {
                return $this->errorHandler->error('Invalid agent ID');
            }

            $result = $this->orchestrator->stopAgent($agentId);

            if ($result['status'] === 'error') {
                return $this->errorHandler->error('Failed to stop agent', $result['message']);
            }

            $this->logger->info("Agent stopped: $agentId");

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Stop agent failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to stop agent', $e->getMessage());
        }
    }

    /**
     * GET /api/agents/stats
     * Get agent statistics
     */
    public function getStatistics()
    {
        try {
            $stats = $this->orchestrator->getStatistics();

            return [
                'status' => 'success',
                'data' => $stats
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get statistics failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get statistics', $e->getMessage());
        }
    }
}
