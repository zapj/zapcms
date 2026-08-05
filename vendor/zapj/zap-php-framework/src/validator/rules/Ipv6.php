<?php

namespace zap\validator\rules;

class Ipv6 extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        return filter_var($value, \FILTER_VALIDATE_IP, \FILTER_FLAG_IPV6) !== false;
    }

}