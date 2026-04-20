<?php

namespace zap\util;


use zap\traits\SingletonTrait;

class Html
{

    use SingletonTrait;

    public static function decode($text) {
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }

    public static function encode($text) {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function tag($tag,$content = null,$options = []) {

    }

    public function docType($type = 'html5'){
        switch ($type){
            case 'html4-strict':
                return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            case 'html4-trans':
                return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
            case 'html5':
            default:
                return '<!DOCTYPE html>';
        }
    }

    public function style($data,$newline = false){

    }

    public function br() {
        return '<br/>';
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::instance(),$name],$arguments);
    }

}