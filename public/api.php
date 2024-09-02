<?php

require_once __DIR__ . '/vendor/autoload.php';

set_error_handler(array('Controller', 'errorHandler'));

$redis = new RedisVue;
$controller = new Controller($redis);

if (array_key_exists('action', $_GET)) {
    $action = $_GET['action'];
    $action = 'action' . ucfirst($action);
    if (method_exists($controller, $action)) {
        $controller->$action();
    }
} else {
    $controller->unknownAction();
}

$controller->display();
