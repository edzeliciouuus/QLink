<?php
/**
 * Database Connection Class
 * PDO-based database connection with singleton pattern
 */

class Database {
    private static $instance = null;
    private $connection;
    private $lastQuery;
    private $lastError;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Database connection failed: " . $this->lastError);
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                throw new Exception("Database connection failed: " . $this->lastError);
            }
            throw new Exception("Database connection failed");
        }
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute a query with parameters
     */
    public function query($sql, $params = []) {
        try {
            $this->lastQuery = $sql;
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Query failed: " . $this->lastError . " SQL: " . $sql);
            throw new Exception("Database query failed: " . $this->lastError);
        }
    }

    /**
     * Fetch a single row
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch a single column value
     */
    public function fetchColumn($sql, $params = [], $column = 0) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn($column);
    }

    /**
     * Insert data and return last insert ID
     */
    public function insert($table, $data) {
        $columns = array_keys($data);
        $placeholders = ':' . implode(', :', $columns);
        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";
        
        $this->query($sql, $data);
        return $this->connection->lastInsertId();
    }

    /**
     * Update data
     */
    public function update($table, $data, $where, $whereParams = []) {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        
        $sql = "UPDATE {$table} SET " . implode(', ', $setParts) . " WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Delete data
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Check if record exists
     */
    public function exists($table, $where, $params = []) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        $count = $this->fetchColumn($sql, $params);
        return $count > 0;
    }

    /**
     * Count records
     */
    public function count($table, $where = '1', $params = []) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        return $this->fetchColumn($sql, $params);
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollback();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction() {
        return $this->connection->inTransaction();
    }

    /**
     * Get last query executed
     */
    public function getLastQuery() {
        return $this->lastQuery;
    }

    /**
     * Get last error
     */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Escape string for LIKE queries
     */
    public function escapeLike($string) {
        return str_replace(['%', '_'], ['\\%', '\\_'], $string);
    }

    /**
     * Build WHERE clause for search
     */
    public function buildSearchWhere($searchFields, $searchTerm) {
        if (empty($searchTerm)) {
            return '1';
        }
        
        $escapedTerm = $this->escapeLike($searchTerm);
        $conditions = [];
        
        foreach ($searchFields as $field) {
            $conditions[] = "{$field} LIKE :search";
        }
        
        return '(' . implode(' OR ', $conditions) . ')';
    }

    /**
     * Build pagination query
     */
    public function paginate($sql, $page = 1, $perPage = 20, $params = []) {
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) FROM ({$sql}) as count_table";
        $dataSql = $sql . " LIMIT {$perPage} OFFSET {$offset}";
        
        $total = $this->fetchColumn($countSql, $params);
        $data = $this->fetchAll($dataSql, $params);
        
        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total)
        ];
    }

    /**
     * Execute raw SQL (use with caution)
     */
    public function executeRaw($sql) {
        try {
            $this->lastQuery = $sql;
            return $this->connection->exec($sql);
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Raw SQL execution failed: " . $this->lastError . " SQL: " . $sql);
            throw new Exception("Raw SQL execution failed: " . $this->lastError);
        }
    }

    /**
     * Get table structure
     */
    public function getTableStructure($table) {
        $safeTable = str_replace('`', '``', $table);
        $sql = "DESCRIBE `{$safeTable}`";
        return $this->fetchAll($sql);
    }

    /**
     * Get table columns
     */
    public function getTableColumns($table) {
        $safeTable = str_replace('`', '``', $table);
        $sql = "SHOW COLUMNS FROM `{$safeTable}`";
        $columns = $this->fetchAll($sql);
        return array_column($columns, 'Field');
    }

    /**
     * Check if table exists
     */
    public function tableExists($table) {
        // Use literal LIKE pattern; PDO/MariaDB do not support bound params in SHOW statements reliably
        $pattern = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $table);
        $sql = "SHOW TABLES LIKE '" . $pattern . "'";
        $result = $this->fetchAll($sql);
        return !empty($result);
    }

    /**
     * Get database size
     */
    public function getDatabaseSize() {
        $sql = "SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
                FROM information_schema.tables 
                WHERE table_schema = :dbname";
        
        return $this->fetchColumn($sql, ['dbname' => DB_NAME]);
    }

    /**
     * Optimize table
     */
    public function optimizeTable($table) {
        $sql = "OPTIMIZE TABLE {$table}";
        return $this->executeRaw($sql);
    }

    /**
     * Close connection
     */
    public function close() {
        $this->connection = null;
        self::$instance = null;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization (must be public in PHP)
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
}

/**
 * Helper function to get database instance
 */
function db() {
    return Database::getInstance();
}
