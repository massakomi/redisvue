<?php

class RedisVue
{
    public Redis $redis;
    private int $idleTime;

    /**
     * @throws RedisException
     */
    public function __construct()
    {

    }

    public function connect()
    {
        $_ENV = parse_ini_file(dirname(__DIR__) . '/.env');
        $this->redis = new \Redis();
        $this->redis?->connect($_ENV['REDIS_HOST']);
    }

    public function getMainInfo(): array
    {
        $info = [
            [
                'code' => 'host',
                'value' => $this->redis->getHost(),
                'comment' => 'Host or unix socket that we are connected to'
            ],
            [
                'code' => 'port',
                'value' => $this->redis->getPort(),
                'comment' => 'Port we are connected to'
            ],
            [
                'code' => 'DbNum',
                'value' => $this->redis->getDbNum(),
                'comment' => 'Database number phpredis is pointed to'
            ],
            [
                'code' => 'timeout',
                'value' => $this->redis->getTimeout(),
                'comment' => 'Write timeout in use for phpredis'
            ],
            [
                'code' => 'readTimeout',
                'value' => $this->redis->getReadTimeout(),
                'comment' => 'Read timeout specified to phpredis or FALSE if were not connected'
            ],
            [
                'code' => 'persistentID',
                'value' => $this->redis->getPersistentID(),
                'comment' => 'Persistent ID that phpredis is using'
            ],
            [
                'code' => 'auth',
                'value' => $this->redis->getAuth(),
                'comment' => 'Password (or username and password if using Redis 6 ACLs) used to authenticate the connection.'
            ],
            [
                'value' => date('Y-m-d H:i:s', $this->redis->lastSave()),
                'comment' => 'Last disk save'
            ],
            [
                'value' => $this->redis->dbSize(),
                'comment' => 'Current database\'s size'
            ],
        ];
        $info = array_filter($info, fn($item) => $item['value'] != 0);
        return $info;
    }

    public function test() {
        $this->redis->set('xxx', 'yyy');
        $data = $this->getData();
        echo '<pre>'.print_r($data, true).'</pre>';
    }

    /**
     * Возвращает такие ключи, как redis_version os config_file executable total_system_memory_human
     * @return mixed|Redis
     * @throws RedisException
     */
    public function getInfo()
    {
        $info = $this->redis->info();
        return array_filter($info, fn($value) => $value !== '' && $value != 0);
    }

    /**
     * Возвращает конфиг
     * @return mixed|Redis
     * @throws RedisException
     */
    public function getConfig()
    {
        $config = $this->redis->config('GET', '*');
        ksort($config);
        return $config;
    }


    public function getIdleTime($allKeys): int
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

    public function getData(): array
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
            $allValues []= $this->getDataValue($key, $type);
        }
        return $allValues;
    }

    private function getDataValue($key, $type)
    {
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
        return $data;
    }

    // $this->redis->acl('USERS')  список пользователей
}