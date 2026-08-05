<?php

namespace zap\validator\rules;

class In extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        if(is_array($this->params) && in_array($value,$this->params)){
            return true;
        }
        return false;
    }

    public function translateParams()
    {
        return '['.join('-',$this->params) .']';
    }


}