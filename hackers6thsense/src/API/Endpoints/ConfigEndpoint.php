<?php
/**
 * Configuration Endpoint
 */

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\Analysis\ConfigAnalyzer;
use PfSenseAI\API\Router;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Utils\ErrorHandler;

class ConfigEndpoint extends Router
{
    private $errorHandler;

    public function __construct()
    {
        parent::__construct();
        $this->errorHandler = new ErrorHandler();
    }

    public function getRules()
    {
        try {
            $analyzer = new ConfigAnalyzer();
            $result = $analyzer->analyze();

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ConfigEndpoint::getRules');
        }
    }

    public function analyze()
    {
        try {
            $analyzer = new ConfigAnalyzer();
            $result = $analyzer->analyze();

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ConfigEndpoint::analyze');
        }
    }

    public function getRecommendations()
    {
        try {
            $input = self::getInput();
            
            Validator::clearErrors();
            $type = Validator::validateAnalysisType($input['type'] ?? null) ?? 'security';
            
            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $analyzer = new ConfigAnalyzer();
            $result = $analyzer->getRecommendations($type);

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ConfigEndpoint::getRecommendations');
        }
    }
}
