<?php

namespace zap\validator\rules;

class Length extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        $length = mb_strlen($value);
        if (is_array($this->params) && count($this->params) == 2) {
            return $length >= $this->params[0] && $length <= $this->params[1];
        }
        return $length == (is_array($this->params) ? $this->params[0] : $this->params);
    }

}