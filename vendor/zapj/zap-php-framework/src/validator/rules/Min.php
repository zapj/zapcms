<?php

namespace zap\validator\rules;

class Min extends \zap\validator\AbstractRule
{
    public function validate($name, $value)
    {
        if (!is_numeric($value)) {
            return false;
        }
        return $value >= $this->params;
    }
}