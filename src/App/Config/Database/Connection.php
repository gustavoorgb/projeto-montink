<?php

namespace App\Config\Database;

use PDO;
use PDOException;

final class Connection {

    private static ?PDO $conn = null;

    private function __construct() {
    }

    public static function getConnection(): ?PDO {
        if (self::$conn === null) {
            try {
                $host = getenv('DB_HOST');
                $user = getenv('DB_USER');
                $pass = getenv('DB_PASS');
                $dbName = getenv('DB_NAME');
                $port = getenv('DB_PORT') ?: 3306;
                self::$conn = new PDO("mysql:host=$host;port=$port;dbname=$dbName", $user, $pass);
                self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conn->exec("SET NAMES utf8mb4");
            } catch (PDOException $e) {
                throw new \RuntimeException("Erro ao conectar ao banco de dados" . $e->getMessage(), 500, $e);
            }
        }
        return self::$conn;
    }
}
