<?php
class Database {
    private $config;
    private $pdo;

    public function __construct() {
        // Load the configuration file
        $this->config = require 'config.php';
        
        // Check if the config was loaded properly
        if (!is_array($this->config) || !isset($this->config['db'])) {
            throw new Exception("Configuration file is not set up correctly.");
        }

        // Extract database connection details from the config
        $dbConfig = $this->config['db'];
        $host = $dbConfig['host'];
        $dbname = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'];
        $charset = $dbConfig['charset'];

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Create a PDO instance
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}
?>