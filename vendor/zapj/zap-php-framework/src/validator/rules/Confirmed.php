<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

/**
 * 验证字段值与确认字段值是否匹配（如 password 与 password_confirmation）
 */
class Confirmed extends AbstractRule
{

    public function validate($name, $value)
    {
        $confirmedField = $name . '_confirmation';
        $confirmedValue = $this->validator->getValue($this->validator->data, $confirmedField);

        return $value === $confirmedValue;
    }

    public function translateParams()
    {
        return $this->params ?: '';
    }

}
