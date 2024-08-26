<?php
$redis = new \Redis();
try {
    $conn = $redis->connect('redis');
} catch (\RedisException $e) {
    exit(json_encode(['error' => $e->getMessage()]));
}
phpinfo();