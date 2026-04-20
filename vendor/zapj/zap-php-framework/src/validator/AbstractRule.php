<?php

namespace zap\validator;

abstract class AbstractRule
{

    /**
     * @var \zap\validator\Validator
     */
    protected $validator;

    protected $params;


    public function __construct($validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param  mixed  $params
     */
    public function setParams($params): void
    {
        $this->params = $params;
    }

    public function validate($name,$value){
        return false;
    }

    public function translateParams(){
        return $this->params;
    }

    public function translateMsgKey(){
        $message = basename(get_called_class());
        $message = strtolower(preg_replace("#([A-Z])#","_$1",$message));
        return 'rule'.$message;
    }
}