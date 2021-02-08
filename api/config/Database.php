<?php

namespace Api\config;

use PDO;
use PDOException;

class Database
{

    // parameters
    private string $host = "localhost";
    private string $db_name = "val8828_testbase";
    private string $username = "val8828_testbase";
    private string $password = "Ncg%34";

    // get base connection
    public function getConnection(): ?PDO
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ];

        $conn = null;

        try {
            $conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password, $options);
            $conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $conn;
    }
}
