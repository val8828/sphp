<?php

namespace API\config;

use PDO;
use PDOException;

class Database
{
    private ?PDO $connection;

    //Called automatically upon initiation
    function __construct()
    {
        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ];

            // database load config and set connection
            $host = M_DB_HOST;
            $db_name = M_DB_NAME;
            $username = M_DB_USER;
            $password = M_DB_PASS;

            $this->connection = new PDO("mysql:host=$host;dbname=$db_name", $username, $password, $options);
            $this->connection->exec("set names utf8");

        } catch (PDOException $e) {
            file_put_contents("log/dberror.log", "Date: " . date('M j Y - G:i:s') . " ---- Error: " . $e->getMessage().PHP_EOL, FILE_APPEND);
            echo "Connection error: " . $e->getMessage();
        }
    }

    /**
     * @return PDO
     */
    public function getConnection(): ?PDO
    {
        return $this->connection;

    }

    function __destruct() {
        try {
            $this->connection = null; //Closes connection
        } catch (PDOException $e) {
            file_put_contents("log/dberror.log", "Date: " . date('M j Y - G:i:s') . " ---- Error: " . $e->getMessage().PHP_EOL, FILE_APPEND);
            echo "Disconnection error: " . $e->getMessage();
        }
    }
}
