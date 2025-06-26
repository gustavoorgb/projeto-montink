<?php

namespace App\Config\Database;

use PDO;
use PDOException;

class Connection {

    protected static ?PDO $instance = null;

    public function __construct() {

        if (is_null(static::$instance)) {
            $host = 'localhost';
            $dbname = 'montink';
            $user = 'userprojeto';
            $pass = 'userpass';
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        }

        try {
            self::$instance = new PDO($dsn, $user, $pass);
            self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro na conexão: " . $e->getMessage());
        }
    }

    protected function getInstance(): PDO {
        return self::$instance;
    }
}
