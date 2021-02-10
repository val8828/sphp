<?php
$router->setBasePath('/v1');

$router->map('POST', '/auth', 'API\Controllers\Controller#register', 'user_reg');
$router->map('POST', '/something', 'API\Controllers\Controller#createEntity', 'add_entity');
$router->map('GET', '/something/[i:id]', 'API\Controllers\Controller#getEntity', 'get_entity');
$router->map('PUT', '/something/[i:id]', 'API\Controllers\Controller#updateEntity', 'update_entity');
$router->map('DELETE', '/something/[i:id]', 'API\Controllers\Controller#deleteEntity', 'delete_entity');
$router->map('DELETE', '/something/[i:id]/safe', 'API\Controllers\Controller#safeDeleteEntity', 'safe_delete_entity');
$router->map('GET', '/something/search', 'API\Controllers\Controller#searchEntity', 'search_entity');
