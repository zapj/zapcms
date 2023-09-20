<?php

namespace zap;

use ArrayObject;
use ReflectionClass;

define('ZAP_SRC', realpath(__DIR__));

class Console
{
    protected $basePath;

    protected $logger = [];

    protected static $instance;

    protected static $container;

    public function __construct($basePath){
        if ($this->isWin()) {
            $this->basePath = str_replace('\\', '/', $basePath);
        } else {
            $this->basePath = $basePath;
        }
        self::$instance = $this;
        static::$container = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);
        define('BASE_PATH',$this->basePath);
    }

    public function __get($key){
        if(isset(static::$container[$key])){
            return static::$container[$key];
        }
        return null;
    }

    public function __set($key,$value){
        static::$container[$key] = $value;
    }

    public function __has($key){
        return isset(static::$container[$key]);
    }

    public function isWin(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    public function isConsole(): bool
    {
        return php_sapi_name() == 'cli';
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function getLogger($name = 'zap'){
        if(!class_exists('\Monolog\Logger')){
            throw new \Exception('Class not found [\Monolog\Logger]');
        }
        if(isset($this->logger[$name])){
            return $this->logger[$name];
        }
        $this->logger[$name] = new \Monolog\Logger($name);
        $handlerClass = config("log.{$name}.handler");
        $params = config("log.{$name}.params",[]);
        if(!class_exists($handlerClass)){
            throw new \Exception('Class not found ['.$handlerClass.']');
        }
        $class = new ReflectionClass($handlerClass);
        if(!($class->isSubclassOf('\Monolog\Handler\HandlerInterface'))){
            throw new \Exception('['.$handlerClass.'] must implement \Monolog\Handler\HandlerInterface interface ');
        }
        $handler = $class->newInstanceArgs($params);

        $this->logger[$name]->pushHandler($handler);
        return $this->logger[$name];
    }
}