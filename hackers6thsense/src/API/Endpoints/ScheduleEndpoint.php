<?php

namespace PfSenseAI\API\Endpoints;

use PfSenseAI\Agents\AgentScheduler;
use PfSenseAI\Agents\FilterManager;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Utils\Logger;
use PfSenseAI\Utils\ErrorHandler;

/**
 * Schedule Endpoint
 * REST API for schedule and filter management
 */
class ScheduleEndpoint
{
    private $scheduler;
    private $filterManager;
    private $validator;
    private $logger;
    private $errorHandler;

    public function __construct()
    {
        $this->scheduler = new AgentScheduler();
        $this->filterManager = new FilterManager();
        $this->validator = new Validator();
        $this->logger = new Logger('schedule-endpoint');
        $this->errorHandler = new ErrorHandler();
    }

    /**
     * POST /api/schedules
     * Create new schedule
     */
    public function createSchedule()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $agentId = $input['agent_id'] ?? null;
            $frequency = $input['frequency'] ?? 'daily';
            $config = $input['config'] ?? [];

            if (!$agentId || !$this->validator->validateString($agentId, 1, 100)) {
                return $this->errorHandler->error('Invalid or missing agent_id');
            }

            $scheduleId = $this->scheduler->createSchedule($agentId, $frequency, $config);

            $this->logger->info("Schedule created: $scheduleId");

            return [
                'status' => 'success',
                'data' => ['schedule_id' => $scheduleId]
            ];
        } catch (\Exception $e) {
            $this->logger->error("Create schedule failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to create schedule', $e->getMessage());
        }
    }

    /**
     * GET /api/schedules
     * List all schedules
     */
    public function getSchedules()
    {
        try {
            $isActive = isset($_GET['active']) ? (bool)$_GET['active'] : null;
            $schedules = $this->scheduler->getSchedules($isActive);

            return [
                'status' => 'success',
                'data' => $schedules,
                'count' => count($schedules)
            ];
        } catch (\Exception $e) {
            $this->logger->error("List schedules failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to list schedules', $e->getMessage());
        }
    }

    /**
     * GET /api/schedules/{id}
     * Get specific schedule
     */
    public function getSchedule($scheduleId)
    {
        try {
            if (!$this->validator->validateInteger($scheduleId)) {
                return $this->errorHandler->error('Invalid schedule ID');
            }

            $schedule = $this->scheduler->getSchedule($scheduleId);

            if (!$schedule) {
                return $this->errorHandler->error('Schedule not found', '', 404);
            }

            return [
                'status' => 'success',
                'data' => $schedule
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get schedule failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get schedule', $e->getMessage());
        }
    }

    /**
     * PUT /api/schedules/{id}
     * Update schedule
     */
    public function updateSchedule($scheduleId)
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$this->validator->validateInteger($scheduleId)) {
                return $this->errorHandler->error('Invalid schedule ID');
            }

            $this->scheduler->updateSchedule($scheduleId, $input);

            $this->logger->info("Schedule updated: $scheduleId");

            return [
                'status' => 'success',
                'message' => 'Schedule updated'
            ];
        } catch (\Exception $e) {
            $this->logger->error("Update schedule failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to update schedule', $e->getMessage());
        }
    }

    /**
     * DELETE /api/schedules/{id}
     * Delete schedule
     */
    public function deleteSchedule($scheduleId)
    {
        try {
            if (!$this->validator->validateInteger($scheduleId)) {
                return $this->errorHandler->error('Invalid schedule ID');
            }

            $this->scheduler->deleteSchedule($scheduleId);

            $this->logger->info("Schedule deleted: $scheduleId");

            return [
                'status' => 'success',
                'message' => 'Schedule deleted'
            ];
        } catch (\Exception $e) {
            $this->logger->error("Delete schedule failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to delete schedule', $e->getMessage());
        }
    }

    /**
     * GET /api/schedules/history
     * Get execution history
     */
    public function getExecutionHistory()
    {
        try {
            $limit = min((int)($_GET['limit'] ?? 50), 200);
            $offset = max((int)($_GET['offset'] ?? 0), 0);
            $agentId = $_GET['agent_id'] ?? null;

            $filters = [];
            if ($agentId) {
                $filters['agent_id'] = $agentId;
            }

            $history = $this->scheduler->getExecutionHistory($filters, $limit, $offset);

            return [
                'status' => 'success',
                'data' => $history
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get execution history failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get execution history', $e->getMessage());
        }
    }

    /**
     * POST /api/schedules/execute
     * Execute all scheduled jobs
     */
    public function executeScheduledJobs()
    {
        try {
            $result = $this->scheduler->executeScheduledJobs();

            $this->logger->info("Scheduled jobs executed: " . $result['executed']);

            return [
                'status' => 'success',
                'data' => $result
            ];
        } catch (\Exception $e) {
            $this->logger->error("Execute scheduled jobs failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to execute scheduled jobs', $e->getMessage());
        }
    }

    /**
     * GET /api/schedules/stats
     * Get schedule statistics
     */
    public function getStatistics()
    {
        try {
            $stats = $this->scheduler->getStatistics();

            return [
                'status' => 'success',
                'data' => $stats
            ];
        } catch (\Exception $e) {
            $this->logger->error("Get statistics failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to get statistics', $e->getMessage());
        }
    }

    /**
     * POST /api/filters
     * Create filter
     */
    public function createFilter()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $name = $input['name'] ?? null;
            $type = $input['type'] ?? null;
            $conditions = $input['conditions'] ?? [];

            if (!$name || !$this->validator->validateString($name, 1, 100)) {
                return $this->errorHandler->error('Invalid or missing filter name');
            }

            $filterId = $this->filterManager->createFilter($name, $type, $conditions);

            $this->logger->info("Filter created: $filterId - $name");

            return [
                'status' => 'success',
                'data' => ['filter_id' => $filterId]
            ];
        } catch (\Exception $e) {
            $this->logger->error("Create filter failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to create filter', $e->getMessage());
        }
    }

    /**
     * GET /api/filters
     * List filters
     */
    public function getFilters()
    {
        try {
            $type = $_GET['type'] ?? null;
            $filters = $this->filterManager->getFilters($type);

            return [
                'status' => 'success',
                'data' => $filters,
                'count' => count($filters)
            ];
        } catch (\Exception $e) {
            $this->logger->error("List filters failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to list filters', $e->getMessage());
        }
    }

    /**
     * POST /api/filters/apply
     * Apply filters to data
     */
    public function applyFilter()
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);

            $filterIds = $input['filter_ids'] ?? [];
            $data = $input['data'] ?? [];

            if (empty($filterIds) || empty($data)) {
                return $this->errorHandler->error('Missing filter_ids or data');
            }

            $result = $this->filterManager->applyFilters($filterIds, $data);

            return [
                'status' => 'success',
                'data' => $result,
                'filtered_count' => count($result)
            ];
        } catch (\Exception $e) {
            $this->logger->error("Apply filter failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to apply filter', $e->getMessage());
        }
    }

    /**
     * DELETE /api/filters/{id}
     * Delete filter
     */
    public function deleteFilter($filterId)
    {
        try {
            if (!$this->validator->validateInteger($filterId)) {
                return $this->errorHandler->error('Invalid filter ID');
            }

            $this->filterManager->deleteFilter($filterId);

            $this->logger->info("Filter deleted: $filterId");

            return [
                'status' => 'success',
                'message' => 'Filter deleted'
            ];
        } catch (\Exception $e) {
            $this->logger->error("Delete filter failed: " . $e->getMessage());
            return $this->errorHandler->error('Failed to delete filter', $e->getMessage());
        }
    }
}
