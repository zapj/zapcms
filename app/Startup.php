<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app;

use Exception;
use zap\exception\NotFoundException;
use zap\http\Middleware;
use zap\http\Router;
use zap\Node;
use zap\Option;

class Startup implements Middleware
{

    protected $options;
    public $router;
    public $currentUri;
    public $baseUrl;
    private $controller;
    private string $method;
    private string $controllerClass;
    private bool $notFound = false;
    private bool $hasParams;

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

        $website = get_options('website','REGEXP');
        //加载 配置
        app()->set('options_website',$website);

        config('config.theme',$website['website.theme'] ?? 'basic');

        $this->parseUrlPath();
        if(!isset($this->controllerClass) || $this->notFound){
//            $website_route = $website['website.route'] ?? 1;
//            $this->initRoute($website_route);
            $this->initRoute();
        }

        if ( !isset($this->controllerClass) || ! class_exists($this->controllerClass)) {
            $this->router->trigger404();

            return false;
        }



        try {
//            app()->controller = new $this->controllerClass();
            app()->make($this->controllerClass, [], 'controller');
            call_user_func_array([app()->controller, 'setParams'], ['params' => $this->router->params]);
            if (method_exists(app()->controller, '_invoke')) {
                call_user_func_array([app()->controller, '_invoke'],
                    ['method' => $this->method,
                        'params' => $this->router->params]
                );
            } else {
                if (method_exists(app()->controller, $this->method)) {
                    call_user_func_array([app()->controller, $this->method], $this->router->params);
                } else {
                    throw new NotFoundException('not found');
                }
            }
        }catch (NotFoundException $e) {
            if (method_exists(app()->controller, '_notfound')) {
                call_user_func_array([app()->controller, '_notfound'],['method' => $this->method,'params' => $this->router->params]);
            } else {
                $this->router->trigger404();
            }
        } catch (Exception $e){
            $this->router->trigger404();
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

        if (isset($segments[0]) && preg_match('/^[a-z]+[-_0-9a-z]+$/i', $segments[0])) {
            $controllerClass = $namespace.'\\'. Router::convertToName($segments[0]) . 'Controller';
            if(class_exists($controllerClass)){
                $this->controllerClass = $controllerClass;
                unset($segments[0]);
            }else{
                $this->notFound = true;
            }
            if (isset($segments[1]) && preg_match('/^[a-z][a-z0-9-_]+$/i', $segments[1])) {
                $this->method = Router::convertToName($segments[1]);
                unset($segments[1]);
            }
        }

        $this->hasParams = !((count($segments) == 0));
        $this->router->params = $segments;
    }

    private function initRoute(): void
    {
        if(($segment = current($this->router->params)) !== false){
            switch ($segment){
                case 'tags':
                    page()->tags = true;
                    $this->resetRoute('node', 'tags');
                    return;
                case 'tag':
                    page()->tag = true;
                    $this->resetRoute('node', 'tag');
                    return;
            }
        }
        $slug  = end($this->router->params);
        if($slug){
            $node = Node::where('slug',$slug)->fetch(FETCH_ASSOC);
            $node or $this->router->trigger404();
            page()->node = $node;
            $this->resetRoute($node['node_type'], 'index');
            page()->nodeType = $node['node_type'];
            page()->nodeMimeType = $node['mime_type'];
            return;
        }
        page()->isHome = true;
        $this->resetRoute('index', 'index');
    }

    private function resetRoute($controller,$action): bool
    {
        $namespace = $this->options['namespace'];
        $this->controller = $controller;
        $this->method = $action;
        $this->controllerClass = $namespace.'\\'. Router::convertToName($this->controller) . 'Controller';
        return true;
    }
}