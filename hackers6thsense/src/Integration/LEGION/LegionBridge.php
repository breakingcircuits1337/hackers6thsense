<?php

namespace PfSenseAI\Integration\LEGION;

use PfSenseAI\Utils\Logger;
use PfSenseAI\Database\Database;
use GuzzleHttp\Client;

/**
 * LEGION Framework Integration Bridge
 * Integrates LEGION's AI blue team defense with Hackers6thSense agents
 */
class LegionBridge
{
    private $logger;
    private $db;
    private $httpClient;
    private $legionEndpoint;
    private $apiKey;

    public function __construct()
    {
        $this->logger = new Logger('legion-bridge');
        $this->db = Database::getInstance();
        $this->legionEndpoint = $_ENV['LEGION_ENDPOINT'] ?? 'http://localhost:3000';
        $this->apiKey = $_ENV['LEGION_API_KEY'] ?? null;
        
        $this->httpClient = new Client([
            'base_uri' => $this->legionEndpoint,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => $this->apiKey ? "Bearer {$this->apiKey}" : ''
            ]
        ]);
    }

    /**
     * Start a LEGION blue team defender session
     */
    public function startDefenderSession($threatLevel = 2, $provider = 'groq')
    {
        try {
            $response = $this->httpClient->post('/api/defenders/start', [
                'json' => [
                    'threatLevel' => $threatLevel,
                    'provider' => $provider,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'integrationSource' => 'Hackers6thSense'
                ]
            ]);

            $data = json_decode($response->getBody(), true);
            
            $this->logger->info("LEGION defender session started: " . $data['sessionId']);
            
            return [
                'status' => 'success',
                'sessionId' => $data['sessionId'],
                'defenderId' => $data['defenderId'],
                'threat_level' => $threatLevel,
                'provider' => $provider
            ];
        } catch (\Exception $e) {
            $this->logger->error("Failed to start LEGION session: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Analyze threat with LEGION blue team
     */
    public function analyzeThreat($threatData, $sessionId = null)
    {
        try {
            $payload = [
                'threat' => $threatData,
                'timestamp' => date('Y-m-d H:i:s'),
                'sessionId' => $sessionId
            ];

            $response = $this->httpClient->post('/api/defenders/analyze', [
                'json' => $payload
            ]);

            $analysis = json_decode($response->getBody(), true);
            
            // Store analysis in database
            $this->db->insert('legion_analysis', [
                'session_id' => $sessionId,
                'threat_data' => json_encode($threatData),
                'analysis' => json_encode($analysis),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info("Threat analyzed via LEGION: " . json_encode($analysis));
            
            return [
                'status' => 'success',
                'analysis' => $analysis
            ];
        } catch (\Exception $e) {
            $this->logger->error("LEGION threat analysis failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Get defense recommendations from LEGION
     */
    public function getDefenseRecommendations($threatData, $sessionId = null)
    {
        try {
            $response = $this->httpClient->post('/api/defenders/recommend', [
                'json' => [
                    'threat' => $threatData,
                    'sessionId' => $sessionId
                ]
            ]);

            $recommendations = json_decode($response->getBody(), true);
            
            return [
                'status' => 'success',
                'recommendations' => $recommendations['actions'] ?? [],
                'priority' => $recommendations['priority'] ?? 'medium'
            ];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get LEGION recommendations: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Correlate agent execution with LEGION threat intelligence
     */
    public function correlateAgentWithThreatIntel($agentId, $executionId, $agentResults)
    {
        try {
            // Get threat intel from LEGION
            $threatIntel = $this->fetchThreatIntelligence();
            
            // Correlate with agent results
            $correlation = [
                'agent_id' => $agentId,
                'execution_id' => $executionId,
                'agent_results' => $agentResults,
                'threat_intel' => $threatIntel,
                'correlation_score' => $this->calculateCorrelationScore($agentResults, $threatIntel),
                'timestamp' => date('Y-m-d H:i:s')
            ];

            // Store correlation
            $this->db->insert('legion_correlations', [
                'agent_id' => $agentId,
                'execution_id' => $executionId,
                'correlation' => json_encode($correlation),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return [
                'status' => 'success',
                'correlation' => $correlation
            ];
        } catch (\Exception $e) {
            $this->logger->error("Correlation failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Fetch threat intelligence from LEGION
     */
    public function fetchThreatIntelligence()
    {
        try {
            $response = $this->httpClient->get('/api/defenders/threat-intel');
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $this->logger->warn("Could not fetch LEGION threat intel: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get LEGION defender status
     */
    public function getDefenderStatus($sessionId = null)
    {
        try {
            $endpoint = '/api/defenders/status';
            if ($sessionId) {
                $endpoint .= "?sessionId=$sessionId";
            }

            $response = $this->httpClient->get($endpoint);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $this->logger->error("Failed to get defender status: " . $e->getMessage());
            return ['status' => 'error'];
        }
    }

    /**
     * Send alert to LEGION
     */
    public function sendAlert($alertData)
    {
        try {
            $response = $this->httpClient->post('/api/defenders/alerts', [
                'json' => array_merge($alertData, [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'source' => 'Hackers6thSense'
                ])
            ]);

            $result = json_decode($response->getBody(), true);
            $this->logger->info("Alert sent to LEGION: " . $result['alertId']);
            
            return ['status' => 'success', 'alertId' => $result['alertId']];
        } catch (\Exception $e) {
            $this->logger->error("Failed to send alert to LEGION: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Calculate correlation score between agent results and threat intel
     */
    private function calculateCorrelationScore($agentResults, $threatIntel)
    {
        if (empty($threatIntel)) {
            return 0;
        }

        $score = 0;
        $matches = 0;

        // Simple correlation: check for matching indicators
        if (isset($agentResults['findings']) && isset($threatIntel['indicators'])) {
            foreach ($threatIntel['indicators'] as $indicator) {
                if (strpos(json_encode($agentResults['findings']), $indicator) !== false) {
                    $matches++;
                }
            }
            $score = min(100, ($matches / max(count($threatIntel['indicators']), 1)) * 100);
        }

        return round($score, 2);
    }

    /**
     * Get LEGION analytics
     */
    public function getAnalytics($timeframe = 'day')
    {
        try {
            $response = $this->httpClient->get("/api/defenders/analytics?timeframe=$timeframe");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            $this->logger->error("Failed to fetch LEGION analytics: " . $e->getMessage());
            return ['status' => 'error'];
        }
    }
}
