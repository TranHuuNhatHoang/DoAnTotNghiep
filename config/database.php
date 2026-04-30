<?php
require_once __DIR__ . '/env.php';

class Database {
    private $host;
    private $user;
    private $pass;
    private $dbname;
    private $port;
    public $conn;

    public function __construct() {
        $this->host = AppEnv::get('DB_HOST', '127.0.0.1');
        $this->user = AppEnv::get('DB_USER', 'root');
        $this->pass = AppEnv::get('DB_PASS', '');
        $this->dbname = AppEnv::get('DB_NAME', 'web_test');
        $this->port = (int) AppEnv::get('DB_PORT', 3307);
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
            if ($this->conn->connect_error) {
                throw new Exception($this->conn->connect_error);
            }
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Khong the ket noi database. Vui long kiem tra cau hinh.");
        }

        return $this->conn;
    }
}
?>
