<?php

namespace zap\view;

use \Exception;

class TwigViewRenderer extends ViewRenderer
{
    protected $loader;
    protected $twigEngine;
    public function __construct($view)
    {
        parent::__construct($view);
        $this->loader = new \Twig\Loader\FilesystemLoader(View::paths());
        $this->twigEngine = new \Twig\Environment($this->loader, [
            'cache' => config('config.twig_cache',false),
            'debug' => config('config.debug',false),
        ]);

    }

    public function render($return = false)
    {
        foreach (View::$globalData as $name => $value){
            $this->view->params[$name] = $value;
        }
        if($return){
            return $this->twigEngine->render(basename($this->view->viewFile), $this->view->params);
        }
        $this->twigEngine->display(basename($this->view->viewFile), $this->view->params);
    }




}