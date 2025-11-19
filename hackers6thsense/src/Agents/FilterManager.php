<?php

namespace PfSenseAI\Agents;

use PfSenseAI\Utils\Logger;
use PfSenseAI\Utils\Validator;
use PfSenseAI\Database\Database;

/**
 * Filter Manager
 * Advanced filtering with 8 filter types
 */
class FilterManager
{
    private $logger;
    private $validator;
    private $db;

    public function __construct()
    {
        $this->logger = new Logger('filters');
        $this->validator = new Validator();
        $this->db = Database::getInstance();
    }

    /**
     * Create a new filter
     */
    public function createFilter($name, $type, $conditions)
    {
        if (!$this->validator->validateString($name, 1, 100)) {
            throw new \Exception('Invalid filter name');
        }

        $validTypes = [
            'agent_category',
            'severity_level',
            'target_range',
            'result_type',
            'status',
            'date_range',
            'success_rate',
            'custom'
        ];

        if (!in_array($type, $validTypes)) {
            throw new \Exception('Invalid filter type: ' . $type);
        }

        $filterId = $this->db->insert('filters', [
            'name' => $name,
            'type' => $type,
            'conditions' => json_encode($conditions),
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->logger->info("Filter created: $filterId - $name");
        return $filterId;
    }

    /**
     * Get all filters
     */
    public function getFilters($type = null)
    {
        $where = [];
        if ($type) {
            $where['type'] = $type;
        }
        return $this->db->find('filters', $where);
    }

    /**
     * Get specific filter
     */
    public function getFilter($filterId)
    {
        return $this->db->findById('filters', $filterId);
    }

    /**
     * Apply single filter to results
     */
    public function applyFilter($filterId, $data)
    {
        $filter = $this->getFilter($filterId);
        if (!$filter) {
            throw new \Exception('Filter not found: ' . $filterId);
        }

        $conditions = json_decode($filter['conditions'], true);
        return $this->filterData($data, $filter['type'], $conditions);
    }

    /**
     * Apply multiple filters (AND logic)
     */
    public function applyFilters($filterIds, $data)
    {
        $result = $data;

        foreach ($filterIds as $filterId) {
            $result = $this->applyFilter($filterId, $result);
        }

        return $result;
    }

    /**
     * Update filter
     */
    public function updateFilter($filterId, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('filters', $data, ['id' => $filterId]);
        
        $this->logger->info("Filter updated: $filterId");
        return true;
    }

    /**
     * Delete filter
     */
    public function deleteFilter($filterId)
    {
        $this->db->delete('filters', ['id' => $filterId]);
        $this->logger->info("Filter deleted: $filterId");
        return true;
    }

    /**
     * Create preset filters
     */
    public function createPresetFilters()
    {
        $presets = [
            // Agent Category Filter
            [
                'name' => 'Reconnaissance Only',
                'type' => 'agent_category',
                'conditions' => ['categories' => ['reconnaissance']]
            ],
            // Severity Level Filter
            [
                'name' => 'Critical Only',
                'type' => 'severity_level',
                'conditions' => ['levels' => ['critical']]
            ],
            // Status Filter
            [
                'name' => 'Completed Executions',
                'type' => 'status',
                'conditions' => ['statuses' => ['completed']]
            ],
            // Date Range Filter
            [
                'name' => 'Last 24 Hours',
                'type' => 'date_range',
                'conditions' => ['days' => 1]
            ],
            // Success Rate Filter
            [
                'name' => 'High Success Rate',
                'type' => 'success_rate',
                'conditions' => ['min_rate' => 80]
            ],
            // Result Type Filter
            [
                'name' => 'Vulnerability Results',
                'type' => 'result_type',
                'conditions' => ['types' => ['vulnerability', 'weakness']]
            ]
        ];

        foreach ($presets as $preset) {
            try {
                $this->createFilter($preset['name'], $preset['type'], $preset['conditions']);
            } catch (\Exception $e) {
                $this->logger->warn("Could not create preset: " . $e->getMessage());
            }
        }

        return ['preset_filters_created' => count($presets)];
    }

    /**
     * Filter data based on type and conditions
     */
    private function filterData($data, $type, $conditions)
    {
        if (!is_array($data)) {
            $data = [$data];
        }

        switch ($type) {
            case 'agent_category':
                return $this->filterByAgentCategory($data, $conditions);
            case 'severity_level':
                return $this->filterBySeverity($data, $conditions);
            case 'target_range':
                return $this->filterByTargetRange($data, $conditions);
            case 'result_type':
                return $this->filterByResultType($data, $conditions);
            case 'status':
                return $this->filterByStatus($data, $conditions);
            case 'date_range':
                return $this->filterByDateRange($data, $conditions);
            case 'success_rate':
                return $this->filterBySuccessRate($data, $conditions);
            case 'custom':
                return $this->filterByCustom($data, $conditions);
            default:
                return $data;
        }
    }

    private function filterByAgentCategory($data, $conditions)
    {
        $categories = $conditions['categories'] ?? [];
        return array_filter($data, fn($item) => in_array($item['category'] ?? null, $categories));
    }

    private function filterBySeverity($data, $conditions)
    {
        $levels = $conditions['levels'] ?? [];
        $levelMap = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        
        return array_filter($data, function($item) use ($levels, $levelMap) {
            $itemLevel = $levelMap[$item['severity'] ?? 'low'] ?? 0;
            $conditionLevels = array_map(fn($l) => $levelMap[$l] ?? 0, $levels);
            return in_array($itemLevel, $conditionLevels);
        });
    }

    private function filterByTargetRange($data, $conditions)
    {
        $targets = $conditions['targets'] ?? [];
        return array_filter($data, fn($item) => in_array($item['target'] ?? null, $targets));
    }

    private function filterByResultType($data, $conditions)
    {
        $types = $conditions['types'] ?? [];
        return array_filter($data, fn($item) => in_array($item['result_type'] ?? null, $types));
    }

    private function filterByStatus($data, $conditions)
    {
        $statuses = $conditions['statuses'] ?? [];
        return array_filter($data, fn($item) => in_array($item['status'] ?? null, $statuses));
    }

    private function filterByDateRange($data, $conditions)
    {
        $days = $conditions['days'] ?? 7;
        $cutoffDate = (new \DateTime())->sub(new \DateInterval("P{$days}D"));
        
        return array_filter($data, function($item) use ($cutoffDate) {
            $itemDate = new \DateTime($item['created_at'] ?? 'now');
            return $itemDate >= $cutoffDate;
        });
    }

    private function filterBySuccessRate($data, $conditions)
    {
        $minRate = $conditions['min_rate'] ?? 0;
        $maxRate = $conditions['max_rate'] ?? 100;
        
        return array_filter($data, function($item) use ($minRate, $maxRate) {
            $rate = $item['success_rate'] ?? 0;
            return $rate >= $minRate && $rate <= $maxRate;
        });
    }

    private function filterByCustom($data, $conditions)
    {
        // Custom filter implementation
        $field = $conditions['field'] ?? null;
        $operator = $conditions['operator'] ?? '=';
        $value = $conditions['value'] ?? null;

        if (!$field) {
            return $data;
        }

        return array_filter($data, function($item) use ($field, $operator, $value) {
            $itemValue = $item[$field] ?? null;

            switch ($operator) {
                case '=':
                    return $itemValue == $value;
                case '!=':
                    return $itemValue != $value;
                case '>':
                    return $itemValue > $value;
                case '<':
                    return $itemValue < $value;
                case '>=':
                    return $itemValue >= $value;
                case '<=':
                    return $itemValue <= $value;
                case 'contains':
                    return strpos($itemValue ?? '', $value) !== false;
                case 'in':
                    return in_array($itemValue, (array)$value);
                default:
                    return true;
            }
        });
    }

    /**
     * Get filter statistics
     */
    public function getStatistics()
    {
        $totalFilters = $this->db->count('filters');
        $filtersByType = [];
        
        $allFilters = $this->getFilters();
        foreach ($allFilters as $filter) {
            $type = $filter['type'];
            $filtersByType[$type] = ($filtersByType[$type] ?? 0) + 1;
        }

        return [
            'total_filters' => $totalFilters,
            'filters_by_type' => $filtersByType
        ];
    }
}
