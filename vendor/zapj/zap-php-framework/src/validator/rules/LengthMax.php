<?php

namespace zap\validator\rules;

class LengthMax extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        $length = mb_strlen($value);
        if (is_array($this->params)) {
            return false;
        }
        return $length <= $this->params;
    }

}