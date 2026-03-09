<?php

class Database {
    private $host = 'localhost';
    private $db   = 'kejamtaani';
    private $user = 'root';
    private $pass = '';
    private $charset = 'utf8mb4';

    public function getConnection() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        return new PDO($dsn, $this->user, $this->pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }
}
