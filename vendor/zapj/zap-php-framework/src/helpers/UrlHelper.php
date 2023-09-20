<?php

namespace zap\helpers;

use zap\http\Request;
use zap\util\Str;

use function app;
use function config;

class UrlHelper
{
    public function base($url = null,$fullPath = false){
        return $fullPath ? config('config.site_url','') . app()->baseUrl($url) : app()->baseUrl($url);
    }

    public function home(){
        $prefix = rtrim(app()->router->currentRoute['pattern'],'.*/');
        return app()->router->baseUrl . $prefix;
    }

    public function current(){
        return app()->baseUrl(app()->router->currentUri . (Request::query_string() ? '?'.Request::query_string():''));
    }

    public function route($format,$args = []){
        return app()->router->baseUrl . Str::format($format,$args);
    }

    public function to($format,$params = [],$queryString = true ){
        $prefix = rtrim(app()->router->currentRoute['pattern'],'.*/');
        $url = app()->router->baseUrl . $prefix .$format;

        if (is_array($params) && !$queryString) {
            return Str::format($url, $params);
        }
        return $url . '?' . http_build_query($params);
    }

    public function controller(){
        return app()->dispatcher->controller;
    }

    public function method(){
        return app()->_currentAction ?: app()->method;
    }

    public function active($action,$output = 'active'){
        $currentAction = app()->dispatcher->controller . (app()->dispatcher->method ? '/' . app()->dispatcher->method : '');
        if(app()->_currentAction){
            $currentAction = app()->_currentAction;
        }

        if(is_string($action) && ($action == $currentAction || preg_match("#^{$action}$#i",$currentAction))){
            echo $output;
            return $output ? null : true ;
        }else if(in_array($currentAction, (array)$action)){
            echo $output;
            return $output ? null : true ;
        }
        return false;
    }

    public function action($controller,$queryParams = null,$pathParams = null){
        $prefix = rtrim(app()->router->currentRoute['pattern'],'.*/');
        $baseUrl = app()->router->baseUrl . $prefix;
        [$controller,$action] = explode('@',$controller);
        $controller = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $controller),'-'));
        $action = strtolower(trim(preg_replace('/([A-Z])/', '-$1', $action),'-'));
        if($action){
            $baseUrl .= '/' . $controller . '/' . $action;
        } else if($controller){
            $baseUrl .= '/' . $controller;
        }
        if(is_array($pathParams)){
//            $pathParams = array_map(function($segment){return urlencode($segment);},$pathParams);
//            $baseUrl .= '/' . join('/',$pathParams);
            $baseUrl = Str::format($baseUrl,$pathParams);
        }
        if(is_array($queryParams) && count($queryParams) >0){
            $querystring = http_build_query($queryParams);
            $baseUrl .= $querystring ? '?' . $querystring:'';
        }
        return $baseUrl;
    }

    public function getRouteData($name = null){
        if($name == 'controller'){
            return app()->dispatcher->controller;
        } else if($name == 'action' || $name == 'method'){
            return app()->dispatcher->method;
        } else if($name == 'prefix'){
            return rtrim(app()->router->currentRoute['pattern'],'.*/');
        } else if($name == 'full'){
            $currentAction = app()->dispatcher->controller . '/' . app()->dispatcher->method;
            return rtrim(app()->router->currentRoute['pattern'],'.*/') . '/' . $currentAction;
        }else if($name == 'all'){
            $currentAction = app()->dispatcher->controller . '/' . app()->dispatcher->method;
            return app()->router->baseUrl . rtrim(app()->router->currentRoute['pattern'],'.*/') . '/' . $currentAction;
        }
        $currentAction = app()->dispatcher->controller . '/' . app()->dispatcher->method;
        return $currentAction;
    }



}