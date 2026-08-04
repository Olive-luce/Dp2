<?php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $host = DB_HOST;
        $database = DB_NAME;
        $username = DB_USER;
        $password = DB_PASS;

        $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
        ];

        $this->pdo = new PDO($dsn, $username, $password, $options);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}

$pdo = Database::getInstance()->getConnection();
?>
