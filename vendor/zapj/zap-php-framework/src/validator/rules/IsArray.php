<?php

namespace zap\validator\rules;

class IsArray extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        return is_array($value);
    }



}