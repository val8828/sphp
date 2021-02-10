<?php
require 'vendor/autoload.php';

$router = new AltoRouter();
include_once "api/config/routes.php";

// match current request
$match = $router->match();
//
if ($match === false) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("HTTP/1.1 404 Not Found");
    $res = array("error" => "invalid_request", "error_description" => "The " . $_SERVER['REQUEST_URI'] . " do not exist");
    echo json_encode($res);

} else {

    list($controllerName, $actionName) = explode('#', $match['target']);

    if (is_callable(array($controllerName, $actionName))) {
        //TODO Check if $controllerName is controller class

        //$controller = new $controllerName();
        try {
            $controller = new $controllerName;

            call_user_func_array([$controller, $actionName], array($match['params']));
        }catch (Exception $e) {
            echo $e;
        }
    } else {
        echo "no match";
    }
}