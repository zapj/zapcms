<?php

namespace zap\validator\rules;

class Callback extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        if (is_callable($this->params)) {
            return (bool) call_user_func($this->params, $name, $value);
        }

        if (is_string($this->params) && class_exists($this->params)) {
            $class = $this->params;
            $callback = new $class;
            if (method_exists($callback, 'check')) {
                return (bool) $callback->check($name, $value);
            }
        }

        return false;
    }

}