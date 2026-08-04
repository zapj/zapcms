<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

/**
 * 验证当前字段值与指定字段值不同
 * 参数: 目标字段名
 */
class Different extends AbstractRule
{

    public function validate($name, $value)
    {
        if (!is_string($this->params)) {
            return false;
        }

        $otherValue = $this->validator->getValue($this->validator->data, $this->params);

        return $value !== $otherValue;
    }

    public function translateParams()
    {
        return $this->params ?: '';
    }

}
