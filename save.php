<?php

//$env = parse_ini_file('.env');
//$header = $env["REDIS_HOST"];

// https://redis.io/commands/
// Строки   https://github.com/phpredis/phpredis#keys-and-strings
// списки   https://github.com/phpredis/phpredis#lists
// хеш таблицы    https://github.com/phpredis/phpredis#hashes


class RedisVue
{
    private Redis $redis;
    private $idleTime;

    /**
     * @throws RedisException
     */
    public function __construct()
    {
        $this->redis = new \Redis();
        try {
            $this->redis->connect('127.0.0.1', 6379);
        } catch (\RedisException $e) {
            exit(json_encode(['error' => $e->getMessage()]));
        }
    }

    function getInfo(): array
    {
        $info = [
            'host' => [
                'value' => $this->redis->getHost(),
                'comment' => 'Host or unix socket that we are connected to'
            ],
            'port' => [
                'value' => $this->redis->getPort(),
                'comment' => 'Port we are connected to'
            ],
            'DbNum' => [
                'value' => $this->redis->getDbNum(),
                'comment' => 'Database number phpredis is pointed to'
            ],
            'timeout' => [
                'value' => $this->redis->getTimeout(),
                'comment' => 'Write timeout in use for phpredis'
            ],
            'readTimeout' => [
                'value' => $this->redis->getReadTimeout(),
                'comment' => 'Read timeout specified to phpredis or FALSE if were not connected'
            ],
            'persistentID' => [
                'value' => $this->redis->getPersistentID(),
                'comment' => 'Persistent ID that phpredis is using'
            ],
            'auth' => [
                'value' => $this->redis->getAuth(),
                'comment' => 'Password (or username and password if using Redis 6 ACLs) used to authenticate the connection.'
            ],
        ];
        return $info;
    }

    function getAllInfo() {
        // $config = $this->redis->config('GET', '*');
        // $info = $this->redis->info();
        // $serverInfo = getInfo($this->redis)
        // $this->redis->time()
        // $this->redis->lastSave()
        // $this->redis->dbSize()
        // $this->redis->acl('USERS')
    }


    function getIdleTime($allKeys): int
    {
        $idleTime = 0;
        foreach ($allKeys as $key) {
            if (!$idleTime) {
                $idleTime = $this->redis->object("idletime", $key);
                break;
            }
        }
        return $idleTime;
    }

    function getData(): array
    {
        $allKeys = $this->redis->keys('*');

        $this->idleTime = $this->getIdleTime($allKeys);

        $allValues = [];
        foreach ($allKeys as $key) {
            // get type
            $type = match($this->redis->type($key)) {
                \Redis::REDIS_STRING => 'string',
                \Redis::REDIS_SET => 'set',
                \Redis::REDIS_LIST => 'list',
                \Redis::REDIS_ZSET => 'zset',
                \Redis::REDIS_HASH => 'hash',
                \Redis::REDIS_NOT_FOUND => 'other',
                default => 'other'
            };
            $data = [
                'key' => $key,
                'ttl' => $this->redis->ttl($key),
                'type' => $type
            ];
            if ($type == 'string') {
                $data ['value'] = $this->redis->get($key);
            }
            if ($type == 'list') {
                $data ['len'] = $this->redis->lLen($key);
                $data ['values'] = $this->redis->lRange($key, 0, -1);
            }
            if ($type == 'hash') {
                $data ['values'] = $this->redis->hGetAll($key);
            }
            if ($type == 'set') {
                $data ['values'] = $this->redis->sMembers($key);
            }
            if ($type == 'zset') {
                $data ['values'] = $this->redis->zRange($key, 0, -1);
            }
            $allValues []= $data;
        }
        return $allValues;
    }
}


$redis = new RedisVue;


if ($_GET['get']) {
    $data = $redis->getData();
    //echo '<pre>'.print_r($data, 1).'</pre>';
} else {
    $data = json_decode(file_get_contents('php://input'), true);

}
echo json_encode($data);
