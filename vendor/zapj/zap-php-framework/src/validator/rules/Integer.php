<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class Integer extends AbstractRule
{

    public function validate($name, $value)
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

}