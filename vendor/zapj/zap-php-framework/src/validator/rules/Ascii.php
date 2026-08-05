<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class Ascii extends AbstractRule
{

    public function validate($name, $value)
    {
        // mb_detect_encoding returns 'ASCII' on success (truthy), false on failure
        if (mb_detect_encoding($value, 'ASCII', true)) {
            return true;
        }
        return preg_match('/[^\x00-\x7F]/', $value) === 0;
    }

}