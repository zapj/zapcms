<?php

namespace zap\view;

use \Exception;
use Twig\Loader\FilesystemLoader;

class TwigViewRenderer extends ViewRenderer
{
    protected $loader;
    protected $twigEngine;
    public function __construct($view)
    {
        parent::__construct($view);
        $this->loader = new \Twig\Loader\FilesystemLoader(View::getPath());
        $template_paths = config('twig.template_paths',[]);
        foreach ($template_paths as $namespace=>$templateDir){
            $this->loader->addPath($templateDir,is_int($namespace) ? FilesystemLoader::MAIN_NAMESPACE : $namespace);
        }
        $options = config('twig.options',['cache'=>false,'debug'=>true]);
        $options['debug'] = config('config.debug',false);
        $this->twigEngine = new \Twig\Environment($this->loader, $options);
        $extensions = config('twig.extensions',[]);
        foreach ($extensions as $extension){
            if(is_string($extension) && class_exists($extension)){
                $this->twigEngine->addExtension(new $extension());
            }else if(is_array($extension) && isset($extension[0]) && class_exists($extension[0])){
                $this->twigEngine->addExtension(new $extension[0](...$extension[1]??[]));
            }
        }

    }

    public function render($output = false)
    {
        foreach (View::$globalData as $name => $value){
            $this->view->params[$name] = $value;
        }
        if($output){
            return $this->twigEngine->render(basename($this->view->viewFile), $this->view->params);
        }
        $this->twigEngine->display(basename($this->view->viewFile), $this->view->params);
    }

    public function getLoader(): \Twig\Loader\FilesystemLoader
    {
        return $this->loader;
    }

    public function getEnvironment(): \Twig\Environment
    {
        return $this->twigEngine;
    }






}