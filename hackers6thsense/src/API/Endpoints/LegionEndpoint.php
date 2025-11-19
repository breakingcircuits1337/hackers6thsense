<?php

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\Integration\LEGION\LegionBridge;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Utils\Logger;
use PfSenseAI\Utils\ErrorHandler;

/**
 * LEGION Integration Endpoint
 * REST API for LEGION blue team defender integration
 */
class LegionEndpoint
{
    private $legionBridge;
    private $validator;
    private $logger;
    private $errorHandler;

    public function __construct()
    {
        $this->legionBridge = new LegionBridge();
        $this->validator = new Validator();
        $this->logger = new Logger('legion-endpoint');
        $this->errorHandler = new ErrorHandler();
    }

    /**
     * POST /api/legion/defender/start
     * Start a blue team defender session
     */
    public function startDefender()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $threatLevel = (int)($input['threat_level'] ?? 2);
            $provider = $input['provider'] ?? 'groq';

            if ($threatLevel < 1 || $threatLevel > 5) {
                return $this->errorHandler->error('Threat level must be between 1-5');
            }

            $result = $this->legionBridge->startDefenderSession($threatLevel, $provider);

            if ($result['status'] === 'error') {
                return $this->errorHandler->error('Failed to start defender', $result['message']);
            }

            $this->logger->info("Defender session started: {$result['sessionId']}");

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Start defender failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to start defender', $e->getMessage());
        }
    }

    /**
     * POST /api/legion/analyze
     * Analyze threat with LEGION blue team
     */
    public function analyzeThreat()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $threatData = $input['threat'] ?? null;
            $sessionId = $input['session_id'] ?? null;

            if (!$threatData) {
                return $this->errorHandler->error('Missing threat data');
            }

            $result = $this->legionBridge->analyzeThreat($threatData, $sessionId);

            if ($result['status'] === 'error') {
                return $this->errorHandler->error('Analysis failed', $result['message']);
            }

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Threat analysis failed: " . $e->getMessage());
            return $this->errorHandler->error('Analysis failed', $e->getMessage());
        }
    }

    /**
     * POST /api/legion/recommendations
     * Get defense recommendations from LEGION
     */
    public function getRecommendations()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $threatData = $input['threat'] ?? null;
            $sessionId = $input['session_id'] ?? null;

            if (!$threatData) {
                return $this->errorHandler->error('Missing threat data');
            }

            $result = $this->legionBridge->getDefenseRecommendations($threatData, $sessionId);

            if ($result['status'] === 'error') {
                return $this->errorHandler->error('Failed to get recommendations', $result['message']);
            }

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get recommendations failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get recommendations', $e->getMessage());
        }
    }

    /**
     * POST /api/legion/correlate
     * Correlate agent execution with threat intelligence
     */
    public function correlateWithThreatIntel()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $agentId = $input['agent_id'] ?? null;
            $executionId = $input['execution_id'] ?? null;
            $agentResults = $input['agent_results'] ?? null;

            if (!$agentId || !$executionId || !$agentResults) {
                return $this->errorHandler->error('Missing required fields');
            }

            $result = $this->legionBridge->correlateAgentWithThreatIntel(
                $agentId,
                $executionId,
                $agentResults
            );

            if ($result['status'] === 'error') {
                return $this->errorHandler->error('Correlation failed', $result['message']);
            }

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Correlation failed: " . $e->getMessage());
            return $this->errorHandler->error('Correlation failed', $e->getMessage());
        }
    }

    /**
     * GET /api/legion/threat-intel
     * Get threat intelligence from LEGION
     */
    public function getThreatIntel()
    {
        try {
            $threatIntel = $this->legionBridge->fetchThreatIntelligence();

            return [
                'status' => 'success',
                'data' => $threatIntel
            ];
        } catch (\Exception $e) {
            $this->logger->error("Fetch threat intel failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to fetch threat intelligence', $e->getMessage());
        }
    }

    /**
     * GET /api/legion/defender/status
     * Get LEGION defender status
     */
    public function getDefenderStatus()
    {
        try {
            $sessionId = $_GET['session_id'] ?? null;
            $status = $this->legionBridge->getDefenderStatus($sessionId);

            return [
                'status' => 'success',
                'data' => $status
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get status failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get status', $e->getMessage());
        }
    }

    /**
     * POST /api/legion/alerts
     * Send alert to LEGION
     */
    public function sendAlert()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $alertType = $input['type'] ?? null;
            $severity = $input['severity'] ?? 'medium';
            $message = $input['message'] ?? null;
            $data = $input['data'] ?? [];

            if (!$alertType || !$message) {
                return $this->errorHandler->error('Missing alert type or message');
            }

            $result = $this->legionBridge->sendAlert([
                'type' => $alertType,
                'severity' => $severity,
                'message' => $message,
                'data' => $data
            ]);

            if ($result['status'] === 'error') {
                return $this->errorHandler->error('Failed to send alert', $result['message']);
            }

            $this->logger->info("Alert sent: " . $result['alertId']);

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Send alert failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to send alert', $e->getMessage());
        }
    }

    /**
     * GET /api/legion/analytics
     * Get LEGION analytics
     */
    public function getAnalytics()
    {
        try {
            $timeframe = $_GET['timeframe'] ?? 'day';

            $analytics = $this->legionBridge->getAnalytics($timeframe);

            return [
                'status' => 'success',
                'data' => $analytics
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get analytics failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get analytics', $e->getMessage());
        }
    }
}
