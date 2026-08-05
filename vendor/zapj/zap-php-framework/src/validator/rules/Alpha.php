<?php

namespace zap\validator\rules;

class Alpha extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {

        return preg_match('/^([a-z])+$/i', $value);;
    }

}