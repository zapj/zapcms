<?php

namespace zap\view;


use zap\exception\ViewNotFoundException;
use zap\util\Str;

class View {

    public $layout = false;

    public $viewName;

    public $viewFile;

    public $params = [];

    public $blocks = [];

    private $_blocksStack = [];

    protected static $templatePaths = [];

    public static $globalData = [];

    /**
     * @var PHPRenderer
     */
    private $engine;



    public function __construct($name = null,$data = []){
        $this->params = $data;
        $this->viewName = $name;

        if(($theme = config('config.theme',false)) !== false){
            array_unshift(static::$templatePaths,themes_path("$theme"));
        }else{
            array_unshift(static::$templatePaths,base_path('app/views'));
        }
        if(config('config.set_theme_include_path',false) === false){
            set_include_path(get_include_path() . PATH_SEPARATOR .  join(PATH_SEPARATOR,static::$templatePaths));
            config_set('config.set_theme_include_path',true);
        }
        $this->prepare($name);
    }

    public static function share($name,$value){
        static::$globalData[$name] = $value;
    }

    public static function set($name,$value){
        static::$globalData[$name] = $value;
    }

    public static function paths($path = null): array
    {
        if($path != null){
            array_unshift(static::$templatePaths,$path);
        }
        return static::$templatePaths;
    }

    public static function getPath(): array
    {
        return static::$templatePaths;
    }

    public static function addPath($path = null,$append = false): void
    {
        if(!$append){
            array_unshift(static::$templatePaths,$path);
        }else{
            static::$templatePaths[] = $path;
        }
    }

    public function __get($name)
    {
        if(isset($this->params[$name])){
            return $this->params[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

    public function __unset($name)
    {
        unset($this->params[$name]);
    }

    public function getViewFile()
    {
        return $this->viewFile;
    }

    public function layout($layout) {
        $this->layout = $this->resolveTemplate($layout);
    }

    public function extend($layout) {
        $this->layout = $this->resolveTemplate($layout);
    }

    public function include($name,$blockName = '_include'){
        $this->engine->_render($this->resolveTemplate($name),$blockName);
    }

    public function block($name) {
        return $this->blocks[$name] ?? '';
    }

    public function beginBlock($name) {
        ob_start();
        $this->_blocksStack[] = $name;
    }

    public function endBlock() {
        $blockName = array_pop($this->_blocksStack);
        $this->blocks[$blockName] = rtrim(ob_get_clean());
    }

    public static function make($name = null,$data = []): View
    {
        return new View($name,$data);
    }

    public function display($output = false){
        return $this->engine->render($output);
    }

    /**
     * @throws ViewNotFoundException
     */
    private function prepare($name): void
    {
        $this->viewFile = $this->resolveTemplate($name);
        if(is_null($this->viewFile)){
            throw new ViewNotFoundException('Template file not found , File name:'.$name);
        }
        $this->initViewRenderer();
    }

    private function resolveTemplate($template): ?string
    {
        $template = str_replace('.','/',$template);
        foreach(static::$templatePaths as $tplPath){
            $tplFullPath = "{$tplPath}/{$template}.php";
            if(is_file($tplFullPath)){
                return $tplFullPath;
            }
            $tplFullPath = "{$tplPath}/{$template}.twig";
            if(is_file($tplFullPath)){
                return $tplFullPath;
            }
        }
        return null;
    }


    /**
     * 渲染模板
     * @param string $template 模板路径
     * @param array $data 参数
     * @param bool $output 返回View内容
     * @return string|null
     */
    public static function render(string $template, array $data = [], bool $output = false): ?string
    {
        $view = View::make($template,$data);

        return $view->display($output);
    }

    private function initViewRenderer(): void
    {
        if(Str::endsWith($this->viewFile,'.twig')){
            $this->engine = new TwigViewRenderer($this);
        }else{
            $this->engine = new PHPRenderer($this);
        }
    }

}