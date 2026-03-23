<?php

class Database {
    private $host = 'sql307.infinityfree.com'; 
    private $db   = 'if0_41455095_kejamtaani'; 
    private $user = 'if0_41455095'; 
    private $pass = 'a8tmr2LRIGbyu8Z'; 
    private $charset = 'utf8mb4';
    public function getConnection() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        try {
            return new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            
            die("Connection failed: " . $e->getMessage());
        }
    }
}