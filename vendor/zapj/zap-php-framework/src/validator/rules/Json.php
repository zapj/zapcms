<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

/**
 * 验证字符串是否为合法 JSON
 * 参数: 可选，'array' 要求解析为数组；'object' 要求解析为对象
 */
class Json extends AbstractRule
{

    public function validate($name, $value)
    {
        if (!is_string($value) || trim($value) === '') {
            return false;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        if ($this->params === 'array' && !is_array($decoded)) {
            return false;
        }

        if ($this->params === 'object' && is_array($decoded)) {
            return false;
        }

        return true;
    }

}
