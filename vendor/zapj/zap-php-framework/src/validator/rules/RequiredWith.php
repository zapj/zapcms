<?php

namespace zap\validator\rules;

use zap\validator\AbstractRule;

class RequiredWith extends AbstractRule
{

    public function validate($name, $value)
    {
        if (is_string($this->params)) {
            $this->params = [$this->params];
        }

        $emptyFieldNum = 0;
        $fieldNum = count($this->params);

        foreach ($this->params as $key) {
            $val = $this->validator->getValue($this->validator->data, $key);
            if (is_null($val) || (is_string($val) && trim($val) === '') || (is_array($val) && empty($val))) {
                $emptyFieldNum++;
            }
        }

        // If any specified field is present, this field is required
        if ($emptyFieldNum < $fieldNum) {
            if (is_null($value)) {
                return false;
            }
            if (is_string($value) && trim($value) === '') {
                return false;
            }
            if (is_array($value) && empty($value)) {
                return false;
            }
            return true;
        }

        return true;
    }

}