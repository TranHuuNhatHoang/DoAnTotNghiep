<?php
class Database {
    private $host = "127.0.0.1";
    private $user = "root";
    private $pass = "";
    private $dbname = "web_test";
    private $port = 3307;
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname, $this->port);
            if ($this->conn->connect_error) {
                die("Kết nối DB thất bại: " . $this->conn->connect_error);
            }
            $this->conn->set_charset("utf8mb4");
        } catch(Exception $e) {
            echo "Lỗi kết nối: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>