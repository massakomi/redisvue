<?php

require_once __DIR__ . '/vendor/autoload.php';



$redis = new RedisVue;

$data = [];

if (array_key_exists('info', $_GET)) {
    $data = $redis->getInfo();
}

if (array_key_exists('get', $_GET)) {
    $data = $redis->getData();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
}

header('Content-Type: application/json');
echo json_encode($data);
