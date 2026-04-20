<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class Email extends AbstractRule
{

    public function validate($name, $value)
    {
        return filter_var($value, \FILTER_VALIDATE_EMAIL) !== false;
    }

}