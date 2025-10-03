<?php

/**
 * Database Configuration and Connection
 * Enhanced database connection with error handling and security
 */

class Database
{
    private static $instance = null;
    private $connection;

    private $host = 'localhost'; // Hostinger database host
    private $username = 'u179926833_imanol'; // Your Hostinger database username
    private $password = 'Midomini0esEste123.@'; // Your Hostinger database password
    private $database = 'u179926833_portfolio'; // Your Hostinger database name
    private $charset = 'utf8mb4';

    private function __construct()
    {
        $this->connect();
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function connect()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset={$this->charset}";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
        ];

        try {
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }

    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function rollback()
    {
        return $this->connection->rollback();
    }
}

// Legacy connection for backward compatibility
function getConnection()
{
    return Database::getInstance()->getConnection();
}

// Global database instance
$db = Database::getInstance();
