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

    public function getSettings()
    {
        try {
            $db = new \PfSenseAI\Database\Database();
            $settings = $db->query("SELECT * FROM system_settings");

            $formattedSettings = [];
            foreach ($settings as $setting) {
                $formattedSettings[$setting['key']] = $setting['value'];
            }

            self::response([
                'status' => 'success',
                'settings' => $formattedSettings
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'SystemEndpoint::getSettings');
        }
    }

    public function saveSettings()
    {
        try {
            $input = self::getInput();
            $db = new \PfSenseAI\Database\Database();

            foreach ($input as $key => $value) {
                // Upsert setting
                $db->query(
                    "INSERT INTO system_settings (key, value, updated_at) VALUES (:key, :value, CURRENT_TIMESTAMP) 
                     ON CONFLICT(key) DO UPDATE SET value = :value, updated_at = CURRENT_TIMESTAMP",
                    ['key' => $key, 'value' => $value]
                );
            }

            self::response([
                'status' => 'success',
                'message' => 'Settings saved successfully'
            ]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'SystemEndpoint::saveSettings');
        }
    }
}
