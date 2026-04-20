<?php

namespace zap\validator\rules;

class Ip extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        return filter_var($value, \FILTER_VALIDATE_IP) !== false;
    }

}