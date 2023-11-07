<?php

namespace zap;

use ArrayObject;
use Exception;
use ReflectionClass;
use zap\http\Router;
use zap\util\Arr;

define('ZAP_SRC', realpath(__DIR__));

class App implements \ArrayAccess
{

    public const VERSION = '1.0.2';

    protected $rootPath;

    protected $basePath;

    protected $baseUrl;

    protected array $logger = [];

    protected static App $instance;

    protected static ArrayObject $container;

    public function __construct($basePath)
    {
        if ($this->isWin()) {
            $this->basePath = str_replace('\\', '/', $basePath) . '/';
            $this->rootPath = str_replace('\\', '/', Arr::get($_SERVER, 'DOCUMENT_ROOT', $basePath)) . '/';
        } else {
            $this->basePath = $basePath . '/';
            $this->rootPath = Arr::get($_SERVER, 'DOCUMENT_ROOT', $basePath);
        }
        self::$instance = $this;
        if(config('config.debug',false)){
            error_reporting(E_ALL ^ E_NOTICE);
        }else{
            error_reporting(0);
        }
        ErrorHandler::register();
        $this->prepare();
    }


    public static function instance(): App
    {
        if ( ! isset(self::$instance)) {
            self::$instance = new App(realpath('../../../'));
        }

        return self::$instance;
    }


    public function baseUrl($path = null): string
    {
        if ($path) {
            return $this->baseUrl.$path;
        }

        return $this->baseUrl;
    }

    public function rootPath($path = null)
    {
        if ($path) {
            return $this->rootPath.$path;
        }

        return $this->rootPath;
    }

    public function basePath($path = null)
    {
        if ($path) {
            return $this->basePath.$path;
        }

        return $this->basePath;
    }

    public function configPath($filename = null): string
    {
        if ($filename) {
            return $this->basePath.'config/'.$filename;
        }

        return $this->basePath.'config/';
    }

    public function assetsPath($filename = null): string
    {
        if ($filename) {
            return $this->basePath.'assets/'.$filename;
        }

        return $this->basePath.'assets/';
    }

    public function storagePath($filename = null): string
    {
        if ($filename) {
            return $this->basePath.'storage/'.$filename;
        }

        return $this->basePath.'storage/';
    }

    public function resourcesPath($filename = null): string
    {
        if ($filename) {
            return $this->basePath.'resources/'.$filename;
        }

        return $this->basePath.'resources/';
    }

    public function themesPath($filename = null): string
    {
        if ($filename) {
            return $this->basePath.'themes/'.$filename;
        }

        return $this->basePath.'themes/';
    }

    public function varPath($filename = null): string
    {
        if ($filename) {
            return $this->basePath.'var/'.$filename;
        }

        return $this->basePath.'var/';
    }

    public function themesUrl($url){
        if($url){
            return $this->baseUrl . '/themes/' . $url;
        }
        return $this->baseUrl . '/themes/';
    }

    public function isWin(): bool
    {
        return DIRECTORY_SEPARATOR === '\\';
    }

    public function isConsole(): bool
    {
        return php_sapi_name() == 'cli';
    }

    protected function prepare()
    {
        $this->baseUrl = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) ;
        static::$container = new ArrayObject([], ArrayObject::ARRAY_AS_PROPS);


        define('ROOT_PATH',$this->rootPath);
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



    /**
     * @return Router
     */
    public function createRouter(): Router
    {
        app()->router = new Router();
        return app()->router;
    }

    public function run(): bool
    {
        $router = Router::create();
        return $router->dispatch();
    }


    /**
     * @throws Exception
     */
    public function getLogger($name = 'app'){
        $name = config('log.default',$name);
        if(isset($this->logger[$name])){
            return $this->logger[$name];
        }

        if(!class_exists('\Monolog\Logger')){
            throw new Exception('Monolog is not installed,  Please run \'composer require monolog/monolog\'');
        }
        $handlerClass = config("log.{$name}.handler");
        try{
            $this->logger[$name] = new \Monolog\Logger($name);
            $params = config("log.{$name}.params",[]);
            $class = new ReflectionClass($handlerClass);
            if(!($class->isSubclassOf('\Monolog\Handler\HandlerInterface'))){
                throw new Exception('['.$handlerClass.'] must implement \Monolog\Handler\HandlerInterface interface ');
            }
            $handler = $class->newInstanceArgs($params);

            $this->logger[$name]->pushHandler($handler);
        }catch (\ReflectionException $e){
            throw new Exception('Class not found ['.$handlerClass.']');
        }

        return $this->logger[$name];
    }


    public function offsetExists($offset): bool
    {
        return isset(static::$container[$offset]);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return static::$container[$offset];
    }

    #[\ReturnTypeWillChange]
    public function offsetSet($offset, $value):void
    {
        static::$container[$offset] = $value;
    }

    #[\ReturnTypeWillChange]
    public function offsetUnset($offset):void{
        unset(static::$container[$offset]);
    }

    public function has($name): bool
    {
        return $this->offsetExists($name);
    }

    public function get($name){
        return $this->offsetGet($name);
    }

    public function set($name,$value){
        $this->offsetSet($name,$value);
    }

    /**
     * @throws Exception
     */
    public function make($class , $args = [] , $alias = null){
        try{
            $instance = new ReflectionClass($class);
            $constructMethod = $instance->getConstructor();
            if($constructMethod && $constructMethod->getNumberOfParameters() > 0){
                $params = $constructMethod->getParameters();
                foreach ($params as $param){
                    if(isset($args[$param->name])){
                        continue;
                    }
                    $args[$param->name] = $this->get($param->name) ?? $this->get($param->getClass()->getName());
                }
                $object = $instance->newInstanceArgs($args);
            }else{
                $object = $instance->newInstance();
            }
            $this->offsetSet($alias ?? $class,$object);

        }catch (\ReflectionException $e){
            throw new Exception("App::make Instance initialization failed,Error:{$e->getMessage()}");
        }
        return $object;
    }
}