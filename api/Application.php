<?php

namespace API;

use AltoRouter;
use API\Controllers\Controller;
use Exception;

class Application
{
    private Response $response;

    public function __construct(array $routeCollections)
    {
        try {
            $router = new AltoRouter($routeCollections);
            $router->setBasePath('/v1');

            // match current request
            $match = $router->match();

            if ($match === false) {

                $this->response = new Response(BAD_REQUEST_CODE,
                    array("error" => "invalid_request",
                        "error_description" => "The " . $_SERVER['REQUEST_URI'] . " do not exist"));

            } else {

                list($controllerName, $actionName) = explode('#', $match['target']);

                if (is_callable(array($controllerName, $actionName))) {
                    $controller = new Controller();

                    $this->response = call_user_func_array([$controller, $actionName], array($match['params']));
                } else {
                    $this->response = new Response(SERVER_ERROR_CODE,
                        array("error" => "SERVER ERROR"));
                }
            }
        } catch (Exception $e) {
            $this->response = new Response(SERVER_ERROR_CODE,
                array("error" => "SERVER ERROR"));
        } finally {

            if (is_null($this->response)) {
                $this->response = new Response(SERVER_ERROR_CODE,
                    array("error" => "SERVER ERROR"));
            }
            $this->sendResponse($this->response);
        }

    }

    private function sendResponse(Response $response = null)
    {
        http_response_code($response->getResponseCode());

        foreach ($response->getHeaders() as $headerName => $headerValue) {
            header($headerName . ": " . $headerValue);
        }

        echo json_encode($response->getResponse());
    }

}