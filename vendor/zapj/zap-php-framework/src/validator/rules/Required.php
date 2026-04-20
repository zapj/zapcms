<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class Required extends AbstractRule
{

    public function validate($name, $value)
    {
        if(!is_array($value)){
            $value = [$value];
        }
        foreach($value as $v){
            if (is_null($v) || (is_string($v) && trim($v) === '')) {
                return false;
            }
        }

        return true;
    }

}