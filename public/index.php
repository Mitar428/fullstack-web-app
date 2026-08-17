<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Medical\Clinic\Core\Session;

Session::start();

$router = require_once __DIR__ . '/../config/routes.php';

$url    = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$router->dispatch($url, $method);