<?php

namespace zap\validator\rules;

class AlphaNum extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {

        return preg_match('/^([a-z0-9])+$/i', $value);
    }

}