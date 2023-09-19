<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class Between extends AbstractRule
{

    public function validate($name, $value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        if (!is_array($this->params) && count($this->params[0]) !== 2) {
            return false;
        }
        $min = $this->params[0];
        $max = $this->params[1];
        return $value >= $min && $value <= $max;


    }

}