<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app;

use zap\exception\NotFoundException;
use zap\http\Middleware;
use zap\http\Router;
use zap\Option;

class Startup implements Middleware
{

    protected $options;
    public $router;
    public $currentUri;
    public $baseUrl;
    /**
     * @var mixed|string
     */
    private $controller;
    private string $method;
    private string $controllerClass;

    public function __construct($options = [])
    {
        $this->options = $options;
        $this->controller = $this->options['controller'] ?? 'index';
        $this->method = $this->options['action'] ?? 'index';
    }

    public function handle()
    {
        define('IN_ZAP_CMS',true);
        app()->page = new Page();
        $website = get_options('^website\.','REGEXP');
        //加载 配置
        app()->set('options_website',$website);

        config('config.theme',$website['website.theme'] ?? 'basic');

        $this->parseUrlPath();

        $website_route = $website['website.route'] ?? 1;
        $this->initRoute($website_route);


        if ( ! class_exists($this->controllerClass)) {
            $this->router->trigger404();

            return false;
        }



        try {
//            app()->controller = new $this->controllerClass();
            app()->make($this->controllerClass,[],'controller');
            call_user_func_array([app()->controller, 'setParams'], ['params'=>$this->router->params]);
            if (method_exists(app()->controller, '_invoke')) {
                call_user_func_array([app()->controller, '_invoke'],
                    ['method' => $this->method,
                        'params' => $this->router->params]
                );
            } else {
                if (method_exists(app()->controller, $this->method)) {
                    call_user_func_array([app()->controller, $this->method],$this->router->params);
                } else {
                    throw new NotFoundException('not found');
                }
            }
        } catch (NotFoundException $e) {
            if (method_exists(app()->controller, '_notfound')) {
                call_user_func_array([app()->controller, '_notfound'],['method' => $this->method,'params' => $this->router->params]);
            } else {
                $this->router->trigger404();
            }
        }
        return false;
    }

    private function parseUrlPath()
    {
        $namespace = $this->options['namespace'];
        $routeBase = rtrim(app()->router->currentRoute['pattern'], '.*');
        $url = trim(
            preg_replace("#$routeBase#iu", '', $this->currentUri, 1), '/ '
        );
        $segments = preg_split('#/#', trim($url, '/'), -1, PREG_SPLIT_NO_EMPTY);
        $controller = array_shift($segments);
        $method = array_shift($segments);

        if ($controller != null && preg_match('/^[a-z]+[-_0-9a-z]+$/i', $controller)) {
            $this->controller = $controller;
            if ($method != null && preg_match('/^[a-z][a-z0-9-_]+$/i', $method)) {
                $this->method = Router::convertToName($method);
            } elseif ($method != null) {
                array_unshift($segments, $method);
            }
        } else {
            array_unshift($segments, $controller);
        }

        $this->hasParams = !((count($segments) == 0));

        $this->router->params = $segments;
        $this->controllerClass = $namespace.'\\'. Router::convertToName($this->controller) . 'Controller';
    }

    private function initRoute($routeType){
        if($routeType === 1){
            page()->nodeId = intval($_GET['p'] ?? 0);
            page()->tags = isset($_GET['tags']);
            page()->tag = $_GET['tag'] ?? 0;
            page()->isHome = (count($_GET) == 0);
            if(page()->isHome){
                $this->resetRoute('index','index');
            }else if(page()->nodeId){
                $this->resetRoute('node','index');
            }else if(page()->tags){
                $this->resetRoute('tags','index');
            }else if(page()->tag){
                $this->resetRoute('tag','index');
            }

        }
    }

    private function resetRoute($controller,$action){
        $namespace = $this->options['namespace'];
        $this->controller = $controller;
        $this->method = $action;
        $this->controllerClass = $namespace.'\\'. Router::convertToName($this->controller) . 'Controller';
    }
}