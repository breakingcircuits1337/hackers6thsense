<?php
/**
 * API Router
 */

namespace PfSenseAI\API;

use PfSenseAI\Utils\Logger;
use PfSenseAI\Utils\ErrorHandler;

class Router
{
    private $logger;
    private $errorHandler;
    private $routes = [];

    public function __construct()
    {
        $this->logger = Logger::getInstance();
        $this->errorHandler = new ErrorHandler();
        $this->registerRoutes();
    }

    private function registerRoutes()
    {
        // Analysis routes
        $this->routes['POST /api/analysis/traffic'] = 'PfSenseAI\API\Endpoints\AnalysisEndpoint@analyzeTraffic';
        $this->routes['GET /api/analysis/traffic/history'] = 'PfSenseAI\API\Endpoints\AnalysisEndpoint@getTrafficHistory';
        $this->routes['GET /api/analysis/anomalies'] = 'PfSenseAI\API\Endpoints\AnalysisEndpoint@detectAnomalies';

        // Threat routes
        $this->routes['GET /api/threats'] = 'PfSenseAI\API\Endpoints\ThreatEndpoint@getThreats';
        $this->routes['POST /api/threats/analyze'] = 'PfSenseAI\API\Endpoints\ThreatEndpoint@analyzeThreat';
        $this->routes['GET /api/threats/dashboard'] = 'PfSenseAI\API\Endpoints\ThreatEndpoint@getDashboard';

        // Configuration routes
        $this->routes['GET /api/config/rules'] = 'PfSenseAI\API\Endpoints\ConfigEndpoint@getRules';
        $this->routes['POST /api/config/analyze'] = 'PfSenseAI\API\Endpoints\ConfigEndpoint@analyze';
        $this->routes['GET /api/recommendations'] = 'PfSenseAI\API\Endpoints\ConfigEndpoint@getRecommendations';

        // Log routes
        $this->routes['GET /api/logs'] = 'PfSenseAI\API\Endpoints\LogEndpoint@getLogs';
        $this->routes['POST /api/logs/analyze'] = 'PfSenseAI\API\Endpoints\LogEndpoint@analyze';
        $this->routes['POST /api/logs/search'] = 'PfSenseAI\API\Endpoints\LogEndpoint@search';
        $this->routes['GET /api/logs/patterns'] = 'PfSenseAI\API\Endpoints\LogEndpoint@getPatterns';

        // Chat routes (Enhanced)
        $this->routes['POST /api/chat'] = 'PfSenseAI\API\Endpoints\ChatEndpoint@send';
        $this->routes['POST /api/chat/multi-turn'] = 'PfSenseAI\API\Endpoints\ChatEndpoint@multiTurn';
        $this->routes['GET /api/chat/history'] = 'PfSenseAI\API\Endpoints\ChatEndpoint@getHistory';
        $this->routes['GET /api/chat/summary'] = 'PfSenseAI\API\Endpoints\ChatEndpoint@getSummary';
        $this->routes['POST /api/chat/clear'] = 'PfSenseAI\API\Endpoints\ChatEndpoint@clearHistory';

        // System routes
        $this->routes['GET /api/system/status'] = 'PfSenseAI\API\Endpoints\SystemEndpoint@status';
        $this->routes['GET /api/system/providers'] = 'PfSenseAI\API\Endpoints\SystemEndpoint@getProviders';

        // Agent routes
        $this->routes['GET /api/agents'] = 'PfSenseAI\API\Endpoints\AgentEndpoint@listAgents';
        $this->routes['GET /api/agents/:id'] = 'PfSenseAI\API\Endpoints\AgentEndpoint@getAgent';
        $this->routes['POST /api/agents/:id/execute'] = 'PfSenseAI\API\Endpoints\AgentEndpoint@executeAgent';
        $this->routes['POST /api/agents/batch/execute'] = 'PfSenseAI\API\Endpoints\AgentEndpoint@executeBatch';
        $this->routes['GET /api/agents/:id/results'] = 'PfSenseAI\API\Endpoints\AgentEndpoint@getResults';
        $this->routes['GET /api/agents/active'] = 'PfSenseAI\API\Endpoints\AgentEndpoint@getActiveAgents';
        $this->routes['POST /api/agents/:id/stop'] = 'PfSenseAI\API\Endpoints\AgentEndpoint@stopAgent';
        $this->routes['GET /api/agents/stats'] = 'PfSenseAI\API\Endpoints\AgentEndpoint@getStatistics';

        // Schedule routes
        $this->routes['POST /api/schedules'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@createSchedule';
        $this->routes['GET /api/schedules'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@getSchedules';
        $this->routes['GET /api/schedules/:id'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@getSchedule';
        $this->routes['PUT /api/schedules/:id'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@updateSchedule';
        $this->routes['DELETE /api/schedules/:id'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@deleteSchedule';
        $this->routes['GET /api/schedules/history'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@getExecutionHistory';
        $this->routes['POST /api/schedules/execute'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@executeScheduledJobs';
        $this->routes['GET /api/schedules/stats'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@getStatistics';

        // Filter routes
        $this->routes['POST /api/filters'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@createFilter';
        $this->routes['GET /api/filters'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@getFilters';
        $this->routes['POST /api/filters/apply'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@applyFilter';
        $this->routes['DELETE /api/filters/:id'] = 'PfSenseAI\API\Endpoints\ScheduleEndpoint@deleteFilter';

        // LEGION Integration routes
        $this->routes['POST /api/legion/defender/start'] = 'PfSenseAI\API\Endpoints\LegionEndpoint@startDefender';
        $this->routes['POST /api/legion/analyze'] = 'PfSenseAI\API\Endpoints\LegionEndpoint@analyzeThreat';
        $this->routes['POST /api/legion/recommendations'] = 'PfSenseAI\API\Endpoints\LegionEndpoint@getRecommendations';
        $this->routes['POST /api/legion/correlate'] = 'PfSenseAI\API\Endpoints\LegionEndpoint@correlateWithThreatIntel';
        $this->routes['GET /api/legion/threat-intel'] = 'PfSenseAI\API\Endpoints\LegionEndpoint@getThreatIntel';
        $this->routes['GET /api/legion/defender/status'] = 'PfSenseAI\API\Endpoints\LegionEndpoint@getDefenderStatus';
        $this->routes['POST /api/legion/alerts'] = 'PfSenseAI\API\Endpoints\LegionEndpoint@sendAlert';
        $this->routes['GET /api/legion/analytics'] = 'PfSenseAI\API\Endpoints\LegionEndpoint@getAnalytics';

        // Oblivion Integration routes - Session & Planning
        $this->routes['POST /api/oblivion/session/start'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@startSession';
        $this->routes['POST /api/oblivion/plan'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@generatePlan';
        $this->routes['GET /api/oblivion/status'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@getStatus';
        
        // Oblivion Integration routes - Attack Execution
        $this->routes['POST /api/oblivion/attack/ddos'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@executeDDoS';
        $this->routes['POST /api/oblivion/attack/sqli'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@executeSQLi';
        $this->routes['POST /api/oblivion/attack/bruteforce'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@executeBruteForce';
        $this->routes['POST /api/oblivion/attack/ransomware'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@executeRansomware';
        $this->routes['POST /api/oblivion/attack/metasploit'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@executeMetasploit';
        
        // Oblivion Integration routes - Social Engineering
        $this->routes['POST /api/oblivion/phishing/generate'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@generatePhishing';
        $this->routes['POST /api/oblivion/disinformation/generate'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@generateDisinformation';
        
        // Oblivion Integration routes - Statistics & Monitoring
        $this->routes['GET /api/oblivion/statistics'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@getStatistics';
        $this->routes['GET /api/oblivion/attacks/recent'] = 'PfSenseAI\API\Endpoints\OblivionEndpoint@getRecentAttacks';
    }

    public function dispatch(string $method, string $path)
    {
        $route = "{$method} {$path}";

        if (isset($this->routes[$route])) {
            $this->handleRoute($this->routes[$route]);
        } else {
            $this->notFound();
        }
    }

    private function handleRoute(string $handler)
    {
        try {
            [$class, $method] = explode('@', $handler);
            
            if (!class_exists($class)) {
                $this->errorHandler->handleException(
                    new \Exception("Handler class not found: $class"),
                    "Router::handleRoute"
                );
            }

            $instance = new $class();
            
            if (!method_exists($instance, $method)) {
                $this->errorHandler->handleException(
                    new \Exception("Handler method not found: $method in $class"),
                    "Router::handleRoute"
                );
            }

            $instance->$method();
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, "Router::handleRoute");
        }
    }

    private function notFound()
    {
        $this->errorHandler->handleNotFound('Endpoint not found');
    }

    protected static function getInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }

    protected static function response(array $data, int $statusCode = 200)
    {
        http_response_code($statusCode);
        // Sanitize response to prevent XSS
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP);
        exit;
    }
}
