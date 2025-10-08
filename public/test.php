<?php


require_once __DIR__ . '/vendor/autoload.php';

$redis = new RedisVue;
$redis->connect();

$info = $redis->getMainInfo();
echo '<pre>'.print_r($info, true).'</pre>';

$redis->test();


