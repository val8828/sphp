<?php

namespace API\Controllers;

use API\Controllers\Handlers\Handler;
use API\Response;

class Controller
{
    public function register(): Response
    {
        $postData = file_get_contents('php://input');
        $data = json_decode($postData, true);
        $userLogin = $data["login"];
        $userPassword = $data["password"];

        if (!is_null($userLogin) && !is_null($userPassword)) {
            $security = new Handler();

            if (!($security->is_userExists($userLogin))) {
                $token = $security->generateToken($userLogin);
                $security->addUserToBase($userLogin, $userPassword, $token);
                //Successful response
                return new Response(SUCCESSFUL_CREATED_CODE,
                    array("token" => $token));

            } else {
                return new Response(BAD_REQUEST_CODE,
                    array("message" => "user already exist"));
            }
        } else {
            return new Response(BAD_REQUEST_CODE,
                array("message" => "incomplete credentials"));
        }
    }

    public function authorize(): Response
    {
        $postData = file_get_contents('php://input');
        $data = json_decode($postData, true);
        $userLogin = $data["login"];
        $userPassword = $data["password"];

        if (!is_null($userLogin) && !is_null($userPassword)) {

            $security = new Handler();

            if (($security->is_userExists($userLogin)) &&
                $security->checkPassword($userLogin, $userPassword)) {

                $token = $security->generateToken($userLogin);
                $security->updateUserTokenInBase($userLogin, $token);

                return new Response(SUCCESSFUL_RESPONSE_CODE,
                    array("token" => $token));

            } else {
                return new Response(BAD_REQUEST_CODE,
                    array("message" => "wrong credentials"));
            }
        } else {
            return new Response(BAD_REQUEST_CODE,
                array("message" => "incomplete credentials"));
        }
    }

    public function createEntity(): Response
    {
        $security = new Handler();
        if ($security->isTokenValid()) {
            $postData = file_get_contents('php://input');
            $data = json_decode($postData, true);
            $entityHandler = new Handler();
            $entity = $entityHandler->createEntity($data['field1'], $data['field2'], Handler::extractLoginFromToken());

            return new Response(SUCCESSFUL_CREATED_CODE,
                ["id" => $entity->getId(),
                    "field1" => $entity->getField1(),
                    "field2" => $entity->getField2(),
                    "safedel" => $entity->getSafedel()]);

        } else {
            return new Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        }
    }

    public function deleteEntity($param): Response
    {
        $id = $param ['id'];
        $security = new Handler();
        if ($security->isTokenValid()) {
            $userLogin = Handler::extractLoginFromToken();
            $userId = $security->getUserId($userLogin);

            $entityHandler = new Handler();
            $entityOwnerId = $entityHandler->getEntityOwnerId($id);
            if (count($entityOwnerId) === 1 && $entityOwnerId[0]['userid'] == $userId) {
                $entityHandler->deleteEntity($id);
                return new Response(SUCCESSFUL_RESPONSE_CODE,
                    array("message" => 'successful deleted'));
            } elseif (count($entityOwnerId) > 1) {
                foreach ($entityOwnerId as $owner) {
                    if ($owner['userid'] == $userId) {
                        $entityHandler->deleteUserPrivilegesFromEntity($userId, $id);
                        return new Response(SUCCESSFUL_RESPONSE_CODE,
                            array("message" => 'successful deleted'));
                    }
                }
            }
            return new Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        } else {
            return new  Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        }
    }

    public function safeDeleteEntity($param): Response
    {
        $id = $param ['id'];
        $security = new Handler();
        if ($security->isTokenValid()) {
            $userLogin = Handler::extractLoginFromToken();
            $userId = $security->getUserId($userLogin);

            $entityHandler = new Handler();
            $entityOwnerId = $entityHandler->getEntityOwnerId($id);
            foreach ($entityOwnerId as $owner) {
                if ($owner['userid'] == $userId) {
                    $entityHandler->safeDeleteEntity($id);
                    return new Response(SUCCESSFUL_RESPONSE_CODE,
                        array("message" => 'successful deleted'));
                }
            }
            return new Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        } else {
            return new  Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        }
    }

    public function updateEntity($param): Response
    {
        $id = $param ['id'];
        $security = new Handler();
        if ($security->isTokenValid()) {
            $userLogin = Handler::extractLoginFromToken();
            $userId = $security->getUserId($userLogin);

            $entityHandler = new Handler();
            $entityOwnerId = $entityHandler->getEntityOwnerId($id);
            foreach ($entityOwnerId as $owner) {
                if ($owner['userid'] == $userId) {
                    $postData = file_get_contents('php://input');
                    $data = json_decode($postData, true);
                    $field1 = $data["field1"];
                    $field2 = $data["field2"];
                    $newEntity = $entityHandler->updateEntity($id, $field1, $field2, 0);

                    return new Response(SUCCESSFUL_RESPONSE_CODE,
                        array(
                            "id" => $newEntity->getId(),
                            "field1" => $newEntity->getField1(),
                            "field2" => $newEntity->getField2(),
                            "safedel" => $newEntity->getSafedel(),
                        ));
                }
            }
            return new Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        } else {
            return new  Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        }
    }

    public function searchEntity(): Response
    {
        $security = new Handler();
        if ($security->isTokenValid()) {
            $userLogin = Handler::extractLoginFromToken();
            $userId = $security->getUserId($userLogin);

            $entityHandler = new Handler();
            $entities = $entityHandler->getEntitiesByFields($_GET['field1'], $_GET['field2'], $userId);
            if (count($entities) > 0) {
                return new Response(SUCCESSFUL_RESPONSE_CODE,
                    $entities);
            } else {
                return new Response(NOT_FOUND_CODE,
                    array("message" => "entities not found"));
            }
        } else {
            return new  Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        }
    }

    public function getEntity($param): Response
    {
        $id = $param ['id'];
        $security = new Handler();
        if ($security->isTokenValid()) {
            $userLogin = Handler::extractLoginFromToken();
            $userId = $security->getUserId($userLogin);

            $entityHandler = new Handler();
            $entities = $entityHandler->getEntityById($id, $userId);
            if (count($entities) == 1) {
                return new Response(SUCCESSFUL_RESPONSE_CODE,
                    array(
                        "id" => $entities->getId(),
                        "field1" => $entities->getField1(),
                        "field2" => $entities->getField2(),
                        "safedel" => $entities->getSafedel(),
                    ));
            } else {
                return new Response(NOT_FOUND_CODE,
                    array("message" => "entities not found"));
            }
        } else {
            return new  Response(BAD_REQUEST_CODE,
                array("message" => "wrong credentials"));
        }
    }
}