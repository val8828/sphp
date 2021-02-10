<?php

namespace API\Controllers;

use API\config\Database;
use API\dto\User;
use API\dto\UserDAO;
use API\dto\EntityDao;
use \Firebase\JWT\JWT;
use PDO;

class Controller
{
    private Database $database;
    private ?PDO $databaseConnect;

    private UserDAO $userDao;
    private EntityDao $entityDao;

    public function __construct()
    {
        $this->database = new Database();
        $this->databaseConnect = $this->database->getConnection();

        $this->userDao = new UserDAO();
        $this->entityDao = new EntityDao();
    }
    private function encodePassword($password): ?string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    private function addUserToBase($login, $password, $token)
    {
        $user = new User();

        $user->setPassword($this->encodePassword($password));
        $user->setLogin($login);
        $user->setToken($token);

        $this->userDao->createUser($this->databaseConnect, $user);
    }

    public function register()
    {
        $postData = file_get_contents('php://input');
        $data = json_decode($postData, true);
        $userLogin = $data["login"];
        $userPassword = $data["password"];

        if ($this->checkUserExists($userLogin)) {
            if ($this->isUserValid($userLogin, $userPassword)) {
                $token = $this->generateToken($userLogin);
                $this->updateUserTokenInBase($userLogin, $token);
                $this->successfulResponse(['token' => $token]);
            } else {
                $this->badRequestResponse(['message' => "Login failed."]);
            }
        } else {
            $token = $this->generateToken($userLogin);
            $this->addUserToBase($userLogin, $userPassword, $token);
            $this->successfulCreated(['token' => $token]);
        }
    }

    private function isUserValid(string $userLogin, string $userPassword): bool
    {
        $userPasswordInBase =
            ($this->userDao->getUserPassword($this->databaseConnect, $userLogin));
        return password_verify($userPassword, $userPasswordInBase);
    }

    private function generateToken($login): string
    {
        $secret_key = "SECRET_KEY";//TODO вынести  константы и поместить в .conf
        $issuer_claim = "THE_ISSUER"; //TODO вынести  константы и поместить в .conf
        $audience_claim = "THE_AUDIENCE";//TODO вынести константы и поместить в .conf
        $issuedat_claim = time(); // issued at
        $notbefore_claim = $issuedat_claim + 10; //TODO вынести  константы и поместить в .conf
        $expire_claim = $issuedat_claim + 300; //TODO вынести  константы и поместить в .conf
        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "login" => $login
            ));

        return JWT::encode($token, $secret_key);
    }

    private function updateUserTokenInBase($login, $token)
    {
        $this->userDao->updateUserToken($this->databaseConnect, $login, $token);
    }

    function checkUserExists($login): bool
    {
        return ($this->userDao->getUser($this->databaseConnect, $login)) > 0;
    }

    public function createEntity()
    {
        if ($this->checkToken()) {
            $postData = file_get_contents('php://input');
            $data = json_decode($postData, true);

            $entity = $this->entityDao->createEntity(
                $this->databaseConnect,
                $data["field1"], $data["field2"],
                $this->userDao->getUser($this->databaseConnect , $this->extractLoginFromToken()));

            $this->successfulCreated(["id" => $entity->getId(),
                "field1" => $entity->getField1(),
                "field2" => $entity->getField2(),
                "safedel" => $entity->getSafedel()]);

        } else {
            $this->badRequestResponse(["message" => "Bad Request"]);
        }
    }

    public function deleteEntity($id)
    {
        if ($this->checkToken()) {

            $entity = $this->entityDao->getEntityById($this->databaseConnect, $id['id']);
            $user = $this->userDao->getUser( $this->databaseConnect, $this->extractLoginFromToken());

            if (($entity > 0) && ($user > 0)  &&
                ($this->entityDao->isOwn($this->databaseConnect,
                    $user->getId(),$entity->getId()))) {

                $this->entityDao->deleteEntity($this->databaseConnect, $entity->getId());

            } else {
                $this->notFoundRequest(["message" => "Entity not exist."]);
            }
        } else {
            $this->unauthorizedRequest(["message" => "Login failed."]);
        }
    }

    public function safeDeleteEntity($id)
    {
        if ($this->checkToken()) {
            $entity = $this->entityDao->getEntityById($this->databaseConnect, $id['id']);
            if ($entity > 0) {
                $this->entityDao->safeDeleteEntity($this->databaseConnect, $id['id']);

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
            $entity = $this->entityDao->getEntityById($this->databaseConnect, $id['id']);
            $user = $this->userDao->getUser($this->databaseConnect, $this->extractLoginFromToken());
            if ($entity > 0 && $this->entityDao->isOwn($this->databaseConnect, $user->getId(), $entity->getId())) {

                $postData = file_get_contents('php://input');
                $data = json_decode($postData, true);

                $entityFromBase = $this->entityDao->updateEntity(
                    $this->databaseConnect, $id['id'], $data["field1"], $data["field2"], 0);
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
            echo json_encode($this->entityDao->getEntitiesByFields($this->databaseConnect, $_GET['field1'], $_GET['field2']));

        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed."));
        }
    }

    public function getEntity($id)
    {
        if ($this->checkToken()) {

            $entity = $this->entityDao->getEntityById($this->databaseConnect, $id['id']);
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
                $this->notFoundRequest(["message" => "Entity not exist."]);
            }
        } else {
            $this->unauthorizedRequest(["message" => "Login failed."]);
        }
    }

    public function checkToken() :bool
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
        return false;
    }

    public function extractLoginFromToken() : ?string{
        $secret_key = "SECRET_KEY";//TODO вынести в глобальные константы и поместить в .conf
        $jwt = null;

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];

        $arr = explode(" ", $authHeader);

        $jwt = $arr[1];

        if ($jwt) {

            try {

                JWT::$leeway = 60; // $leeway in seconds

                $decodeData = ((array)(JWT::decode($jwt, $secret_key, array('HS256'))))["data"];

                return ((array)$decodeData)['login'];

            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }




    private function successfulResponse($response)
    {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function successfulCreated($response)
    {
        http_response_code(201);
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function badRequestResponse($response)
    {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function unauthorizedRequest($response)
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    private function notFoundRequest($response)
    {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    private function serverErrorRequest($response)
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}