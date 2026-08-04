<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

/**
 * 验证布尔值 (true, false, 1, 0, "1", "0", "true", "false", "yes", "no", "on", "off")
 */
class Boolean extends AbstractRule
{

    protected static $validValues = [true, false, 0, 1, '0', '1', 'true', 'false', 'yes', 'no', 'on', 'off'];

    public function validate($name, $value)
    {
        return in_array($value, self::$validValues, true);
    }

}
