<?php

namespace zap\view;

use \Exception;

class PHPRenderer extends ViewRenderer
{


    public function render($output = false)
    {
        $this->_render($this->view->viewFile);
        $aliasName = 'content';
        if($this->view->layout){
            $aliasName = 'layout';
            $this->_render($this->view->layout,'layout');
        }
        if ($output) {
            return $this->view->blocks[$aliasName];
        }
        echo $this->view->blocks[$aliasName];
    }

    public function _render($template,$aliasName = 'content')
    {
        $obLevel = ob_get_level();
        $error_level = error_reporting();
        if (config('config.debug', false)) {
            error_reporting(E_ALL ^ E_NOTICE);
        }

        ob_start();
        extract($this->view->params, EXTR_SKIP);
        extract(View::$globalData, EXTR_SKIP);
        try {
            if(!is_file($template)){
                trigger_error("Template File: {$template} not found",E_USER_ERROR);
            }
            include $template;
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            throw $e;
        }
        error_reporting($error_level);
        $this->view->blocks[$aliasName] = ob_get_clean();

    }


}