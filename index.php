<?php

use API\Application;

require 'vendor/autoload.php';

$routeCollections = require 'api/config/routes.php';

$application = new Application($routeCollections);