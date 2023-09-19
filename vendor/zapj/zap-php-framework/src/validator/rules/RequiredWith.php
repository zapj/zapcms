<?php

namespace zap\validator\rules;

class RequiredWith extends \zap\validator\AbstractRule
{

    public function validate($name, $value)
    {
        if(is_string($this->params) ){
            $this->params = [$this->params];
        }
        $emptyFieldNum = 0;
        $fieldNum = count($this->params);
        foreach ($this->params as $key) {
            $val = $this->validator->getValue($this->validator->data, $key);
            if (is_null($val) || mb_strlen($val) <= 0) {
                $emptyFieldNum++;
            }
        }
        if ($emptyFieldNum == 0 && (!is_null($value) && (is_string($value) && trim($value) !== ''))) {
            return true;
        }
        return false;
    }

}