<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

/**
 * 验证数组中的值是否唯一（不重复）
 */
class Distinct extends AbstractRule
{

    public function validate($name, $value)
    {
        if (!is_array($value)) {
            return false;
        }

        return count($value) === count(array_unique($value));
    }

}
