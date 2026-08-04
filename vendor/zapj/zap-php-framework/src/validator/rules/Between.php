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
        if (!is_array($this->params) || count($this->params) !== 2) {
            return false;
        }
        [$min, $max] = $this->params;
        return $value >= $min && $value <= $max;
    }

}