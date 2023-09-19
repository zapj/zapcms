<?php

namespace zap\view;

abstract class ViewRenderer
{

    /**
     * @var View
     */
    protected $view;

    public function __construct($view){
        $this->view = $view;
    }

    public function render($output = false) {

    }

    public function layout($layout) {
        $this->view->layout($layout);
    }

    public function extend($layout) {
        $this->view->extend($layout);
    }


    public function include($name,$blockName = '_include'){
        return $this->view->include($name,$blockName);
    }

    public function block($name) {
        return isset($this->view->blocks[$name]) ? $this->view->blocks[$name] : '';
    }

    public function beginBlock($name) {
        $this->view->beginBlock($name);
    }

    public function endBlock() {
        $this->view->endBlock();
    }


}