<?php

namespace zap\validator\rules;

class Callback extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        if(is_callable($this->params)){
            return call_user_func_array($this->params,$value);
        } else if(class_exists($this->params)){
            $class = $this->params; // class Name
            $callback = new $class;
            if(method_exists($callback,'check')){
                return $callback->check($value);
            }

        }
        return false;
    }

}