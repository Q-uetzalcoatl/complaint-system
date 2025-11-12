<?php
// config/database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'complaint_systemcopy';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "<!-- DEBUG: Database connected successfully -->";
        } catch(PDOException $exception) {
            echo "<!-- DEBUG: Database connection failed: " . $exception->getMessage() . " -->";
            error_log("Database Connection Error: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>