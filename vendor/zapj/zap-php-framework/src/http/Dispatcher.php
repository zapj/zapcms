<?php

namespace zap\http;


use zap\exception\NotFoundException;

class Dispatcher implements Middleware
{

    public $baseUrl;

    public $currentUri;

    /**
     * @var \zap\http\Router
     */
    public $router;

    public $controller;

    private $controllerClass;

    public $method;

    public $options;

    public $hasParams = false;

    public function __construct($options)
    {
        $default_params = ['controller' => 'index', 'action' => 'index'];
        $this->options = array_merge($options, $default_params);
        $this->controller = $this->options['controller'];
        $this->method = $this->options['action'];
    }

    public function handle()
    {
        $this->parseUrlPath();

        if ( ! class_exists($this->controllerClass)) {
            $this->router->trigger404();

            return false;
        }

        try {
            app()->controller = new $this->controllerClass();
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
        $routeBase = rtrim($this->router->currentRoute['pattern'], '.*');
        $url = trim(
            preg_replace("#$routeBase#iu", '', $this->currentUri, 1), '/ '
        );
        $segments = preg_split('#/#', trim($url, '/'), -1, PREG_SPLIT_NO_EMPTY);
        $controller = array_shift($segments);
        $method = array_shift($segments);

        if ($controller != null && preg_match('/^[a-z]+[-_0-9a-z]+$/i', $controller)) {
            $this->controller = $controller;
            if ($method != null && preg_match('/^[a-z][a-z0-9-_]+$/i', $method)) {
                $this->method = str_replace('-','',$method);
            } elseif ($method != null) {
                array_unshift($segments, $method);
            }
        } else {
            array_unshift($segments, $controller);
        }

        $this->hasParams = (count($segments) == 0) ? false : true;

        $this->router->params = $segments;
        $this->controllerClass = $namespace.'\\'.str_replace(
                ' ', '',
                ucwords(str_replace(['-', '_'], ' ', $this->controller))
            ) . 'Controller';
    }


}