<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class Numeric extends AbstractRule
{

    public function validate($name, $value)
    {
        return is_numeric($value);
    }

}
