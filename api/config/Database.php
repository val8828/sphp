<?php

namespace API\config;

use PDO;
use PDOException;

class Database
{
    // get base connection
    public function getConnection(): ?PDO
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $conn = null;

        try {
            // database load config and set connection
            $host = M_DB_HOST;
            $db_name = M_DB_NAME;
            $username = M_DB_USER;
            $password = M_DB_PASS;

            $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password, $options);
            $conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $conn;
    }
}
