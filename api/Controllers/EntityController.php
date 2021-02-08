<?php

//require "vendor/autoload.php";

use \Firebase\JWT\JWT;

class EntityController
{
    private $database;

    private $entityDao;

    public function __construct($database)
    {
        $this->database = $database->getConnection();

        $this->entityDao = new EntityDao();

    }

    public function createEntity()
    {
        if ($this->checkToken()) {
            $postData = file_get_contents('php://input');

            $data = json_decode($postData, true);

            $entity = $this->entityDao->createEntity(
                $this->database, $data["field1"], $data["field2"]);

            echo json_encode(
                array(
                    "id" => $entity->getId(),
                    "field1" => $entity->getField1(),
                    "field2" => $entity->getField2(),
                    "safedel" => $entity->getSafedel(),
                )
            );
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed."));
        }
    }

    public function deleteEntity($id)
    {
        if ($this->checkToken()) {
            $entity = $this->entityDao->getEntityById($this->database, $id['id']);
            if ($entity > 0) {
                $this->entityDao->deleteEntity($this->database, $id['id']);
                $entity = $this->entityDao->getEntityById($this->database, $id['id']);
                if ($entity > 0) {
                    http_response_code(500);
                    echo json_encode(array("message" => "Internal error."));
                } else {
                    http_response_code(200);
                    echo json_encode(array("message" => "Successfully delete."));
                }
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Entity not exist."));
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed."));
        }
    }

    public function safeDeleteEntity($id)
    {
        if ($this->checkToken()) {
            $entity = $this->entityDao->getEntityById($this->database, $id['id']);
            if ($entity > 0) {
                $this->entityDao->safeDeleteEntity($this->database, $id['id']);

                http_response_code(200);
                echo json_encode(array("message" => "Successfully delete."));

            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Entity not exist."));
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed."));
        }
    }

    public function updateEntity($id)
    {
        if ($this->checkToken()) {
            $entity = $this->entityDao->getEntityById($this->database, $id['id']);
            if ($entity > 0) {

                $postData = file_get_contents('php://input');

                $data = json_decode($postData, true);

                $entityFromBase = $this->entityDao->updateEntity(
                    $this->database, $id['id'], $data["field1"], $data["field2"], 0);
                echo json_encode(
                    array(
                        "id" => $entityFromBase->getId(),
                        "field1" => $entityFromBase->getField1(),
                        "field2" => $entityFromBase->getField2(),
                        "safedel" => $entityFromBase->getSafedel(),
                    )
                );
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Entity not exist."));
            }

        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed."));
        }
    }

    public function searchEntity()
    {
        if ($this->checkToken()) {
            header("content-type:application/json");
            echo json_encode($this->entityDao->getEntitiesByFields($this->database, $_GET['field1'], $_GET['field2']));

        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed."));
        }
    }

    public function getEntity($id)
    {
        if ($this->checkToken()) {

            $entity = $this->entityDao->getEntityById($this->database, $id['id']);
            if ($entity > 0) {
                echo json_encode(
                    array(
                        "id" => $entity->getId(),
                        "field1" => $entity->getField1(),
                        "field2" => $entity->getField2(),
                        "safedel" => $entity->getSafedel(),
                    )
                );
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Entity not exist."));
            }
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed."));
        }
    }

    public function checkToken()
    {
        $secret_key = "SECRET_KEY";//TODO вынести в глобальные константы и поместить в .conf
        $jwt = null;

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];

        $arr = explode(" ", $authHeader);

        $jwt = $arr[1];

        if ($jwt) {

            try {

                JWT::$leeway = 60; // $leeway in seconds

                JWT::decode($jwt, $secret_key, array('HS256'));//[data[login]]

                return true;

            } catch (Exception $e) {
                return false;
            }
        }
    }
}