<?php

return array(
    array('POST', '/register', 'API\Controllers\Controller#register', 'create_user'),
    array('POST', '/authorize', 'API\Controllers\Controller#authorize', 'authorize_user'),
    array('POST', '/something', 'API\Controllers\Controller#createEntity', 'add_entity'),
    array('GET', '/something/[i:id]', 'API\Controllers\Controller#getEntity', 'get_entity'),
    array('PUT', '/something/[i:id]', 'API\Controllers\Controller#updateEntity', 'update_entity'),
    array('DELETE', '/something/[i:id]', 'API\Controllers\Controller#deleteEntity', 'delete_entity'),
    array('DELETE', '/something/[i:id]/safe', 'API\Controllers\Controller#safeDeleteEntity', 'safe_delete_entity'),
    array('GET', '/something/search', 'API\Controllers\Controller#searchEntity', 'search_entity'),
);