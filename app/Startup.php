<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:09
 * @lastModified 2023/12/20 下午5:50
 *
 */

namespace app;

use Twig\Error\Error;
use zap\cms\models\Node;
use zap\DB;
use zap\exception\NotFoundException;
use zap\exception\ViewNotFoundException;
use zap\http\Middleware;
use zap\http\Router;
use zap\view\View;

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

        $website = get_options('website','REGEXP');
        //加载 配置
        app()->set('options_website',$website);

        config_set('config.theme',$website['website.theme'] ?? 'basic');
        if($website['website.theme'] !== 'basic'){
            View::paths(themes_path('basic'));
        }

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
            app()->controller = new $this->controllerClass();
//            app()->make($this->controllerClass, [], 'controller');
            call_user_func_array([app()->controller, 'setParams'], ['params' => $this->router->params]);
            if (method_exists(app()->controller, '_invoke')) {
                call_user_func_array([app()->controller, '_invoke'],['method' => $this->method,'params' => $this->router->params]);
            } else {
                if (method_exists(app()->controller, $this->method)) {
                    call_user_func_array([app()->controller, $this->method], $this->router->params);
                } else {
                    throw new NotFoundException('not found');
                }
            }
        }catch (NotFoundException $e) {
            if (method_exists(app()->controller, '_notfound')) {
                call_user_func_array([app()->controller, '_notfound'], ['method' => $this->method, 'params' => $this->router->params]);
            } else {
                $this->router->trigger404();
            }
        }catch (ViewNotFoundException $e){
            echo $e->getMessage();
        } catch (Error $e){
            echo $e->getMessage();
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
            if (!$this->notFound && isset($segments[1]) && preg_match('/^[a-z][a-z0-9-_]+$/i', $segments[1])) {
                $this->method = Router::convertToName($segments[1]);
                unset($segments[1]);
            }
        }

        $this->hasParams = !((count($segments) == 0));
        $this->router->params = $segments;
    }

    private function initRoute(): void
    {
        $pageState = pageState();
        if(($segment = current($this->router->params)) !== false){
            switch ($segment){
                case 'tags':
                    $pageState->tags = true;
                    $this->resetRoute('node', 'tags');
                    return;
                case 'tag':
                    $pageState->tag = true;
                    $this->resetRoute('node', 'tag');
                    return;
                case preg_match('/^sitemap([A-Za-z0-9_-]+)?.xml$/i',$segment) === 1:
                    $this->resetRoute('Sitemap', 'generate');
                    return;
            }
        }
        $slug  = end($this->router->params);
        if($slug){
            $node = Node::where('slug',$slug)->where('status',Node::STATUS_PUBLISH)
                ->fetch(FETCH_ASSOC);
            $node or $this->router->trigger404();
            $pageState->node = $node;
            $pageState->nodeId = $node['id'];
            $pageState->nodeType = $node['node_type'];
            $pageState->nodeMimeType = $node['mime_type'];
            if(!$this->resetRoute($node['node_type'], empty($node['mime_type']) ?'index':$node['mime_type'])){
                DB::update('node',['hits'=>DB::raw('hits+1')],['id'=>$node['id']]);
                $this->resetRoute('node', $node['node_type']);
            }
            $pageState->isNode = $node['node_type']!=='catalog';
            $pageState->isCatalog = $node['node_type']==='catalog';
            return;
        }
        $pageState->isHome = true;
        $this->resetRoute('index', 'index');
    }

    private function resetRoute($controller,$action): bool
    {
        $namespace = $this->options['namespace'];
        $this->controller = $controller;
        $this->method = $action;
        $this->controllerClass = $namespace.'\\'. Router::convertToName($this->controller) . 'Controller';
        return class_exists($this->controllerClass);
    }
}