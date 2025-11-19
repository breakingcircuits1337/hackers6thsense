<?php
/**
 * Log Endpoint
 */

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\Analysis\LogAnalyzer;
use PfSenseAI\API\Router;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Utils\ErrorHandler;

class LogEndpoint extends Router
{
    private $errorHandler;

    public function __construct()
    {
        parent::__construct();
        $this->errorHandler = new ErrorHandler();
    }

    public function getLogs()
    {
        try {
            Validator::clearErrors();
            $filter = Validator::validateFilter($_GET['filter'] ?? null);
            $limit = Validator::validateLimit($_GET['limit'] ?? null);
            $offset = Validator::validateOffset($_GET['offset'] ?? null);

            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $analyzer = new LogAnalyzer();
            $result = $analyzer->analyzeLogs($filter, $limit, $offset);

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'LogEndpoint::getLogs');
        }
    }

    public function analyze()
    {
        try {
            $input = self::getInput();
            
            Validator::clearErrors();
            $filter = Validator::validateFilter($input['filter'] ?? null);
            $limit = Validator::validateLimit($input['limit'] ?? null);
            $offset = Validator::validateOffset($input['offset'] ?? null);

            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $analyzer = new LogAnalyzer();
            $result = $analyzer->analyzeLogs($filter, $limit, $offset);

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'LogEndpoint::analyze');
        }
    }

    public function search()
    {
        try {
            $input = self::getInput();
            
            Validator::clearErrors();
            $query = Validator::validateQuery($input['query'] ?? null);

            if (empty($query)) {
                Validator::addError('Query is required');
            }

            if (Validator::hasErrors()) {
                $this->errorHandler->handleValidationError(Validator::getErrors());
            }

            $analyzer = new LogAnalyzer();
            $result = $analyzer->nlSearch($query);

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'LogEndpoint::search');
        }
    }

    public function getPatterns()
    {
        try {
            $analyzer = new LogAnalyzer();
            $result = $analyzer->getPatterns();

            self::response(['data' => $result, 'success' => true]);
        } catch (\Exception $e) {
            $this->errorHandler->handleException($e, 'LogEndpoint::getPatterns');
        }
    }
