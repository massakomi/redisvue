<?php

class Controller
{

    private $data = [];

    public function __construct(private RedisVue $redis)
    {
        try {
            $redis->connect();
        } catch (\Exception $e) {
            $this->data = ['error' => true, 'message' => $e->getMessage()];
        }
        if (str_contains(self::$lastError, 'Redis')) {
            $this->data = ['error' => true, 'message' => self::$lastError];
        }
    }

    public function display()
    {
        header('Content-Type: application/json');
        echo json_encode($this->data);
    }

    public function unknownAction()
    {
        $this->data = ['error' => true, 'message' => 'Unknown action'];
    }

    public function actionData()
    {
        $this->data = $this->redis->getData();
    }

    public function actionInfo()
    {
        $this->data = $this->redis->getMainInfo();
    }

    public function actionSave()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input['type'] === 'string') {
            $options = [];
            if ($input['expired']) {
                $options ['ex'] = $input['expired'];
            }
            $this->redis->redis->set($input['key'], $input['values'][0], $options);
        }
        $this->data = $input;
    }

    static $lastError = false;

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_NOTICE: case E_USER_NOTICE: $error = 'Notice'; break;
            case E_WARNING: case E_USER_WARNING: $error = 'Warning'; break;
            case E_ERROR: case E_USER_ERROR: $error = 'Fatal Error'; break;
            default: $error = 'Unknown'; break;
        }
        self::$lastError = $errstr . " ($error)";
    }
}