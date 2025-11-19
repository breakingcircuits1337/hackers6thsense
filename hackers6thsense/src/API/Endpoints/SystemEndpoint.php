<?php
/**
 * System Endpoint
 */

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\AI\AIFactory;
use PfSenseAI\API\Router;
use PfSenseAI\Utils\ErrorHandler;

class SystemEndpoint extends Router
{
    private $errorHandler;

    public function __construct()
    {
        parent::__construct();
        $this->errorHandler = new ErrorHandler();
    }

    public function status()
    {
        try {
            $aiFactory = AIFactory::getInstance();

            self::response([
                'status' => 'success',
                'application' => 'Hackers6thSense',
                'version' => '1.0.0',
                'current_provider' => $aiFactory->getCurrentProviderName(),
                'available_providers' => $aiFactory->getAvailableProviders(),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'SystemEndpoint::status');
        }
    }

    public function getProviders()
    {
        try {
            $aiFactory = AIFactory::getInstance();

            self::response([
                'status' => 'success',
                'providers' => $aiFactory->getAvailableProviders(),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'SystemEndpoint::getProviders');
        }
    }
}
