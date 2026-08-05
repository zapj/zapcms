<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class Double extends AbstractRule
{

    public function validate($name, $value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

}