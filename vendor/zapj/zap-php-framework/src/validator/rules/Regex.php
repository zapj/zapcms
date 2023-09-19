<?php

namespace zap\validator\rules;

class Regex extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        return preg_match($this->params,$value) !== false;
    }

}