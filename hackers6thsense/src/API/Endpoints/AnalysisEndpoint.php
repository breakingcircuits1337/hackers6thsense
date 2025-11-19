<?php
/**
 * Analysis Endpoint
 */

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\Analysis\TrafficAnalyzer;
use PfSenseAI\API\Router;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Utils\ErrorHandler;

class AnalysisEndpoint extends Router
{
    private $errorHandler;

    public function __construct()
    {
        parent::__construct();
        $this->errorHandler = new ErrorHandler();
    }

    public function analyzeTraffic()
    {
        try {
            $input = self::getInput();
            
            // Validate input parameters
            Validator::clearErrors();
            $timeframe = Validator::validateTimeframe($input['timeframe'] ?? null);
            
            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $analyzer = new TrafficAnalyzer();
            $result = $analyzer->analyze($timeframe);

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'AnalysisEndpoint::analyzeTraffic');
        }
    }

    public function getTrafficHistory()
    {
        try {
            $input = self::getInput();
            
            // Validate input parameters
            Validator::clearErrors();
            $hours = Validator::validateInteger($input['hours'] ?? null, 1, 8760, 'hours') ?? 24;
            
            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $analyzer = new TrafficAnalyzer();
            $result = $analyzer->getHistory($hours);

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'AnalysisEndpoint::getTrafficHistory');
        }
    }

    public function detectAnomalies()
    {
        try {
            $analyzer = new TrafficAnalyzer();
            $result = $analyzer->detectAnomalies();

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'AnalysisEndpoint::detectAnomalies');
        }
    }
}
