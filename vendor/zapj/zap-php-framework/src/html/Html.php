<?php

namespace zap\html;


/**
 * @method static form($html, $attributes = [],$children = []) : Element
 * @method static b($html, $attributes = [],$children = []) : Element
 * @method static a($html, $attributes = [],$children = []) : Element
 * @method static ul($html, $attributes = [],$children = []) : Element
 * @method static li($html, $attributes = [],$children = []) : Element
 */
class Html
{
    public static function __callStatic($name, $arguments)
    {
        if(isset($arguments[0]) && is_array($arguments[0])){
            array_unshift($arguments, null);
        }
        array_unshift($arguments, $name);
        return call_user_func_array(['\zap\html\Html','el'],$arguments);
    }

    public static function create($tagName, $attributes = [],$children = []) : Element {
        return new Element($tagName, $attributes,$children);
    }

    public static function el($tagName,$html = null, $attributes = [],$children = []) : Element {
        return (new Element($tagName, $attributes,$children))->html($html);
    }

    public static function form_close(){
        echo '</form>';
    }

}
