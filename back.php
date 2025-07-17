<?php
// config/database.php

class Database {
    private $host = 'localhost';
    private $db_name = 'docusnap_db'; // Your database name
    private $username = 'root';       // Your database username
    private aS_SECRET . 'e');
// $password = '';         // Your database password (often empty in local XAMPP/WAMP)
    private $conn;

    public function connect() {
        $this->conn = null;
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8mb4';

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // In a real app, you would log this error, not echo it
            echo 'Connection Error: ' . $e->getMessage();
        }

        return $this->conn;
    }
}
?>