<?php
/**
 * Threat Endpoint
 */

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\Analysis\ThreatDetector;
use PfSenseAI\API\Router;
use PfSenseAI\Utils\ErrorHandler;

class ThreatEndpoint extends Router
{
    private $errorHandler;

    public function __construct()
    {
        parent::__construct();
        $this->errorHandler = new ErrorHandler();
    }

    public function getThreats()
    {
        try {
            $detector = new ThreatDetector();
            $result = $detector->detectThreats();

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ThreatEndpoint::getThreats');
        }
    }

    public function analyzeThreat()
    {
        try {
            $input = self::getInput();
            $threatData = $input['threat'] ?? [];

            if (empty($threatData)) {
                $this->errorHandler->handleValidationError(['Threat data is required']);
            }

            $detector = new ThreatDetector();
            $result = $detector->analyzeThreat($threatData);

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ThreatEndpoint::analyzeThreat');
        }
    }

    public function getDashboard()
    {
        try {
            $detector = new ThreatDetector();
            $result = $detector->getDashboard();

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'ThreatEndpoint::getDashboard');
        }
    }
}
