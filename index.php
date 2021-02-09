<?php
require 'vendor/autoload.php';

require 'api/Controllers/Controller.php';
require 'api/config/Database.php';

$router = new AltoRouter();
$router->setBasePath('/v1');

try {
    $router->map('POST', '/auth', 'Controller#register', 'user_reg');
    $router->map('POST', '/something', 'Controller#createEntity', 'add_entity');
    $router->map('GET', '/something/[i:id]', 'EntityController#getEntity', 'get_entity');
    $router->map('PUT', '/something/[i:id]', 'EntityController#updateEntity', 'update_entity');
    $router->map('DELETE', '/something/[i:id]', 'EntityController#deleteEntity', 'delete_entity');
    $router->map('DELETE', '/something/[i:id]/safe', 'EntityController#safeDeleteEntity', 'safe_delete_entity');
    $router->map('GET', '/something/search', 'EntityController#searchEntity', 'search_entity');

} catch (Exception $e) {
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json; charset=UTF-8");
    header("HTTP/1.1 404 Not Found");
    $res = array("error" => "invalid_request", "error_description" => "The " . $_SERVER['REQUEST_URI'] . " do not exist");
    echo json_encode($res);
}

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