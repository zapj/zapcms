<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;
use DateTime;

/**
 * 验证是否为有效日期
 * 参数: 格式字符串（如 'Y-m-d'），默认为 Y-m-d
 */
class Date extends AbstractRule
{

    public function validate($name, $value)
    {
        $format = $this->params ?: 'Y-m-d';

        if ($value instanceof DateTime) {
            return true;
        }

        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        $d = DateTime::createFromFormat($format, $value);
        return $d && $d->format($format) === $value;
    }

}
