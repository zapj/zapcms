<?php

namespace zap\validator\rules;

class Url extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return true;
        }
        return false;
    }

}