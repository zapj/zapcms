<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class NotIn extends AbstractRule
{
    public function validate($name, $value)
    {
        if(is_array($this->params) && !in_array($value,$this->params)){
            return true;
        }
        return false;
    }
}