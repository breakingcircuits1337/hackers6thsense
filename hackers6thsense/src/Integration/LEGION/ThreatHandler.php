<?php

namespace PfSenseAI\Integration\LEGION;

use PfSenseAI\Database\Database;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Client;

/**
 * ThreatHandler - Manages threat escalation and automated responses
 * Bridges LEGION threat analysis with pfSense agent orchestration
 */
class ThreatHandler
{
    private Database $db;
    private LoggerInterface $logger;
    private LegionBridge $legionBridge;
    private LegionConfig $legionConfig;
    private Client $httpClient;

    public function __construct(Database $db, LoggerInterface $logger, LegionBridge $legionBridge, LegionConfig $legionConfig)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->legionBridge = $legionBridge;
        $this->legionConfig = $legionConfig;
        $this->httpClient = new Client();
    }

    /**
     * Handle detected threat with appropriate escalation
     */
    public function handleThreat(array $threatData, int $executionId, int $agentId): array
    {
        $threatLevel = $threatData['threat_level'] ?? 1;
        $confidence = $threatData['confidence'] ?? 0.5;
        $type = $threatData['type'] ?? 'unknown';

        try {
            // Store threat in database
            $threatId = $this->db->insert('legion_analysis', [
                'session_id' => uniqid('threat_'),
                'threat_data' => json_encode($threatData),
                'analysis' => $threatData['analysis'] ?? 'Automated threat analysis',
                'threat_level' => $threatLevel,
                'recommendations' => json_encode($threatData['recommendations'] ?? []),
                'confidence' => $confidence,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info("Threat detected: Type=$type, Level=$threatLevel, Confidence=$confidence%, ExecutionID=$executionId");

            // Determine escalation level
            $escalationLevel = $this->determineEscalation($threatLevel, $confidence);

            // Route handling based on threat level
            switch ($escalationLevel) {
                case 'critical':
                    return $this->handleCriticalThreat($threatData, $threatId, $executionId, $agentId);
                case 'high':
                    return $this->handleHighThreat($threatData, $threatId, $executionId, $agentId);
                case 'medium':
                    return $this->handleMediumThreat($threatData, $threatId, $executionId, $agentId);
                case 'low':
                    return $this->handleLowThreat($threatData, $threatId, $executionId, $agentId);
                default:
                    return $this->handleInfoThreat($threatData, $threatId, $executionId, $agentId);
            }
        } catch (\Exception $e) {
            $this->logger->error("ThreatHandler error: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Determine escalation level based on threat metrics
     */
    private function determineEscalation(int $threatLevel, float $confidence): string
    {
        if ($threatLevel >= 4 && $confidence >= 0.8) {
            return 'critical';
        } elseif ($threatLevel >= 3 && $confidence >= 0.7) {
            return 'high';
        } elseif ($threatLevel >= 2 && $confidence >= 0.6) {
            return 'medium';
        } elseif ($threatLevel >= 1 && $confidence >= 0.5) {
            return 'low';
        }
        return 'info';
    }

    /**
     * Handle critical threats - immediate response required
     */
    private function handleCriticalThreat(array $threatData, int $threatId, int $executionId, int $agentId): array
    {
        try {
            // Store correlation with high severity
            $this->db->insert('legion_correlations', [
                'agent_id' => $agentId,
                'execution_id' => $executionId,
                'correlation' => json_encode(['severity' => 'critical', 'response_type' => 'immediate']),
                'correlation_score' => 0.95,
                'threat_intel' => json_encode($threatData),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Trigger immediate alerts
            $this->legionBridge->sendAlert([
                'level' => 'critical',
                'type' => $threatData['type'] ?? 'unknown',
                'message' => 'CRITICAL THREAT DETECTED - Immediate Response Required',
                'threat_id' => $threatId,
                'execution_id' => $executionId,
                'data' => $threatData,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            // Log critical incident
            $this->logger->critical("CRITICAL THREAT: Type=" . ($threatData['type'] ?? 'unknown') . " ThreatID=$threatId");

            // Notify security team
            $this->notifySecurityTeam($threatData, 'critical');

            // Execute automated containment if configured
            if ($this->legionConfig->getIntegrationMode() === 'active') {
                $this->executeContainment($threatData, $agentId, $executionId);
            }

            return [
                'status' => 'success',
                'escalation' => 'critical',
                'action' => 'immediate_response',
                'threat_id' => $threatId,
                'alert_sent' => true,
                'containment_executed' => $this->legionConfig->getIntegrationMode() === 'active'
            ];
        } catch (\Exception $e) {
            $this->logger->error("Critical threat handling failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle high-level threats
     */
    private function handleHighThreat(array $threatData, int $threatId, int $executionId, int $agentId): array
    {
        try {
            $this->db->insert('legion_correlations', [
                'agent_id' => $agentId,
                'execution_id' => $executionId,
                'correlation' => json_encode(['severity' => 'high', 'response_type' => 'prompt']),
                'correlation_score' => 0.85,
                'threat_intel' => json_encode($threatData),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->legionBridge->sendAlert([
                'level' => 'high',
                'type' => $threatData['type'] ?? 'unknown',
                'message' => 'HIGH PRIORITY THREAT - Prompt Investigation Required',
                'threat_id' => $threatId,
                'data' => $threatData
            ]);

            $this->logger->warning("HIGH THREAT: Type=" . ($threatData['type'] ?? 'unknown') . " ThreatID=$threatId");

            return [
                'status' => 'success',
                'escalation' => 'high',
                'action' => 'prompt_investigation',
                'threat_id' => $threatId
            ];
        } catch (\Exception $e) {
            $this->logger->error("High threat handling failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle medium-level threats
     */
    private function handleMediumThreat(array $threatData, int $threatId, int $executionId, int $agentId): array
    {
        try {
            $this->db->insert('legion_correlations', [
                'agent_id' => $agentId,
                'execution_id' => $executionId,
                'correlation' => json_encode(['severity' => 'medium', 'response_type' => 'monitoring']),
                'correlation_score' => 0.70,
                'threat_intel' => json_encode($threatData),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->info("MEDIUM THREAT: Type=" . ($threatData['type'] ?? 'unknown') . " ThreatID=$threatId");

            return [
                'status' => 'success',
                'escalation' => 'medium',
                'action' => 'enhanced_monitoring',
                'threat_id' => $threatId
            ];
        } catch (\Exception $e) {
            $this->logger->error("Medium threat handling failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle low-level threats
     */
    private function handleLowThreat(array $threatData, int $threatId, int $executionId, int $agentId): array
    {
        try {
            $this->db->insert('legion_correlations', [
                'agent_id' => $agentId,
                'execution_id' => $executionId,
                'correlation' => json_encode(['severity' => 'low', 'response_type' => 'logging']),
                'correlation_score' => 0.55,
                'threat_intel' => json_encode($threatData),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->logger->notice("LOW THREAT: Type=" . ($threatData['type'] ?? 'unknown') . " ThreatID=$threatId");

            return [
                'status' => 'success',
                'escalation' => 'low',
                'action' => 'standard_logging',
                'threat_id' => $threatId
            ];
        } catch (\Exception $e) {
            $this->logger->error("Low threat handling failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle informational threats
     */
    private function handleInfoThreat(array $threatData, int $threatId, int $executionId, int $agentId): array
    {
        try {
            $this->logger->debug("INFO: Type=" . ($threatData['type'] ?? 'unknown') . " ThreatID=$threatId");

            return [
                'status' => 'success',
                'escalation' => 'info',
                'action' => 'logged',
                'threat_id' => $threatId
            ];
        } catch (\Exception $e) {
            $this->logger->error("Info threat handling failed: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Execute automated containment procedures
     */
    private function executeContainment(array $threatData, int $agentId, int $executionId): bool
    {
        try {
            $containmentSteps = $threatData['recommendations'] ?? [];
            
            foreach ($containmentSteps as $step) {
                if (isset($step['action']) && isset($step['target'])) {
                    $this->executeContainmentAction($step['action'], $step['target']);
                }
            }

            $this->logger->info("Containment executed for threat from Agent $agentId (Execution $executionId)");
            return true;
        } catch (\Exception $e) {
            $this->logger->error("Containment execution failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Execute individual containment action
     */
    private function executeContainmentAction(string $action, string $target): bool
    {
        try {
            switch ($action) {
                case 'block_ip':
                    return $this->blockIPAddress($target);
                case 'quarantine':
                    return $this->quarantineTarget($target);
                case 'isolate':
                    return $this->isolateTarget($target);
                case 'throttle':
                    return $this->throttleConnection($target);
                default:
                    $this->logger->warning("Unknown containment action: $action");
                    return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Containment action failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Block IP address in pfSense
     */
    private function blockIPAddress(string $ipAddress): bool
    {
        // Implementation would interact with pfSense XML-RPC API
        $this->logger->info("Blocking IP address: $ipAddress");
        return true;
    }

    /**
     * Quarantine target resource
     */
    private function quarantineTarget(string $target): bool
    {
        $this->logger->info("Quarantining target: $target");
        return true;
    }

    /**
     * Isolate target from network
     */
    private function isolateTarget(string $target): bool
    {
        $this->logger->info("Isolating target: $target");
        return true;
    }

    /**
     * Throttle connection to target
     */
    private function throttleConnection(string $target): bool
    {
        $this->logger->info("Throttling connection to: $target");
        return true;
    }

    /**
     * Notify security team of critical threats
     */
    private function notifySecurityTeam(array $threatData, string $level): bool
    {
        try {
            $webhook = getenv('SECURITY_WEBHOOK_URL');
            if ($webhook) {
                $this->httpClient->post($webhook, [
                    'json' => [
                        'level' => $level,
                        'threat_data' => $threatData,
                        'timestamp' => date('Y-m-d H:i:s'),
                        'channel' => 'hackers6thsense'
                    ]
                ]);
            }

            // Also send email if configured
            $email = getenv('SECURITY_ALERT_EMAIL');
            if ($email) {
                $this->sendSecurityAlertEmail($email, $threatData, $level);
            }

            return true;
        } catch (\Exception $e) {
            $this->logger->error("Security team notification failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email alert to security team
     */
    private function sendSecurityAlertEmail(string $email, array $threatData, string $level): bool
    {
        $subject = "[$level] Security Alert from Hackers6thSense";
        $body = sprintf(
            "Threat Type: %s\nThreat Level: %d/5\nConfidence: %s%%\nTime: %s\n\nDetails:\n%s",
            $threatData['type'] ?? 'Unknown',
            $threatData['threat_level'] ?? 0,
            (int)($threatData['confidence'] ?? 0) * 100,
            date('Y-m-d H:i:s'),
            json_encode($threatData, JSON_PRETTY_PRINT)
        );

        return mail($email, $subject, $body);
    }

    /**
     * Get threat correlation history for agent
     */
    public function getThreatHistory(int $agentId, int $limit = 50): array
    {
        try {
            $sql = "SELECT * FROM legion_correlations WHERE agent_id = :agent_id ORDER BY created_at DESC LIMIT :limit";
            $results = $this->db->query($sql, ['agent_id' => $agentId, 'limit' => $limit]);
            return $results ?? [];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get threat history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get statistics about threats
     */
    public function getThreatStatistics(): array
    {
        try {
            $criticalCount = $this->db->count('legion_analysis', 'threat_level >= 4');
            $highCount = $this->db->count('legion_analysis', 'threat_level = 3');
            $mediumCount = $this->db->count('legion_analysis', 'threat_level = 2');
            $lowCount = $this->db->count('legion_analysis', 'threat_level = 1');

            $avgConfidence = $this->db->query(
                "SELECT AVG(confidence) as avg_confidence FROM legion_analysis",
                []
            )[0]['avg_confidence'] ?? 0;

            return [
                'critical' => $criticalCount,
                'high' => $highCount,
                'medium' => $mediumCount,
                'low' => $lowCount,
                'avg_confidence' => round($avgConfidence * 100, 2),
                'total_threats' => $criticalCount + $highCount + $mediumCount + $lowCount
            ];
        } catch (\Exception $e) {
            $this->logger->error("Failed to get threat statistics: " . $e->getMessage());
            return [];
        }
    }
}
