<?php

namespace PfSenseAI\Database;

use PfSenseAI\Utils\Logger;

/**
 * Database Abstraction Layer
 * Singleton PDO wrapper with query helpers
 */
class Database
{
    private static $instance;
    private $db;
    private $logger;

    private function __construct()
    {
        $this->logger = new Logger('database');
        $this->initDb();
    }

    private function initDb()
    {
        try {
            $dbPath = 'storage/pfsense-ai.db';
            $this->db = new \PDO('sqlite:' . $dbPath);
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Run migrations if needed
            if (!$this->tablesExist()) {
                $migration = new Migration($dbPath);
                $migration->migrate();
            }
        } catch (\Exception $e) {
            $this->logger->error("Database init failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function tablesExist()
    {
        try {
            $result = $this->db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='agents'");
            return $result->fetch() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->db;
    }

    /**
     * Insert record
     */
    public function insert($table, $data)
    {
        $columns = implode(',', array_keys($data));
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $this->db->lastInsertId();
    }

    /**
     * Update record
     */
    public function update($table, $data, $where = [])
    {
        $set = implode(',', array_map(fn($k) => "{$k}=?", array_keys($data)));
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', array_map(fn($k) => "{$k}=?", array_keys($where))) : '';
        
        $sql = "UPDATE {$table} SET {$set} {$whereClause}";
        $stmt = $this->db->prepare($sql);
        
        $params = array_merge(array_values($data), array_values($where));
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    /**
     * Find by ID
     */
    public function findById($table, $id)
    {
        $sql = "SELECT * FROM {$table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Find with conditions
     */
    public function find($table, $where = [], $limit = null, $offset = null)
    {
        $whereClause = '';
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                if (is_array($value)) {
                    $conditions[] = "{$key} IN (" . implode(',', array_fill(0, count($value), '?')) . ")";
                    $params = array_merge($params, $value);
                } else {
                    $conditions[] = "{$key} = ?";
                    $params[] = $value;
                }
            }
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        $sql = "SELECT * FROM {$table} {$whereClause}";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        if ($offset) {
            $sql .= " OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Delete record
     */
    public function delete($table, $where)
    {
        $conditions = [];
        $params = [];
        
        foreach ($where as $key => $value) {
            $conditions[] = "{$key} = ?";
            $params[] = $value;
        }
        
        $whereClause = implode(' AND ', $conditions);
        $sql = "DELETE FROM {$table} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->rowCount();
    }

    /**
     * Count records
     */
    public function count($table, $where = [])
    {
        $whereClause = '';
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $key => $value) {
                $conditions[] = "{$key} = ?";
                $params[] = $value;
            }
            $whereClause = 'WHERE ' . implode(' AND ', $conditions);
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$table} {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['count'] ?? 0;
    }

    /**
     * Raw query
     */
    public function query($sql, $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
