<?php

namespace zap\validator;

use zap\http\Request;
use zap\util\Arr;
use zap\util\Str;
use zap\validator\RuleFactory;

class Validator
{
    protected $rules = [];

    /**
     * 遇到第一个错误停止检测
     * @var bool
     */
    protected $stopFirstFail = false;

    public $data;

    protected $errors = [];

    protected $fieldLabels = [];

    protected $validData = [];

    public function __construct($data = null){
        if($data == null){
            $this->data = Request::method() == 'GET' ? $_GET : $_POST;
        }else{
            $this->data = $data;
        }
    }

    /**
     * @param $ruleName
     * @param $fields
     * @param $params
     *
     * @return \zap\validator\Validator
     */
    public function rule($ruleName,$fields = [],$params = [])
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }
        foreach($fields as $field){
            $this->rules[$ruleName][$field] = $params;
        }
        return $this;
    }

    /**
     * 添加Rule命名空间实现自定义验证规则
     * @param $namespace
     *
     * @return \zap\validator\Validator
     */
    public function addNamespace($namespace){
        RuleFactory::instance()->addNamespace($namespace);
        return $this;
    }

    public function validate(){
        foreach($this->rules as $ruleName => $rule){
            $r = RuleFactory::instance()->make($ruleName,$this);

            foreach ($rule as $field=>$params) {
                $r->setParams($params);
                $value = $this->getValue($this->data, $field);
                $ret = $r->validate($field,$value);
                if(!$ret){
                    $this->addError($field,$ruleName,$value,'validator.'. $r->translateMsgKey(),$r->translateParams());
                }else{
                    $this->validData[$field] = $value;
                }
                if(!$ret && $this->stopFirstFail){
                    break;
                }
            }
        }
        return !((bool)count($this->errors));
    }

    public function getValidData(){
        return $this->validData;
    }

    public function get($name,$default = null){
        return Arr::get($this->validData,$name,$default);
    }



    public function getValue($data,$field)
    {
        $parent_is_wildcard = false;
        foreach (explode('.', $field) as $segment) {
            if (!is_array($data)) {
                return null;
            }

            if($parent_is_wildcard){
                $values = array();
                foreach ($data as $val) {
                    $values[] = $val[$segment];
                }
                $data = $values;
                $parent_is_wildcard = false;
                continue;
            }

            if($segment == '*'){
                $parent_is_wildcard = true;
                $values = array();
                foreach ($data as $val) {
                    $values[] = $val;
                }
                $data = $values;
                continue;
            }

            $data = $data[$segment];

        }
        return $data;
    }

    public function addError($field,$rule,$value, $message, $params = array())
    {
        if(!is_array($params)){
            $params = ['param'=>$params];
        }
        $params['value'] = $value;
        $params['field'] = isset($this->fieldLabels[$field]) ? $this->fieldLabels[$field] : '';
        $this->errors[$field][$rule] = trans($message,$params);
        return $this;
    }

    public function setLabels($field,$title = null){
        if(is_array($field)){
            $this->fieldLabels = array_merge($this->fieldLabels,$field);
        }else{
            $this->fieldLabels[$field] = $title;
        }
        return $this;
    }

    public function reset(){
        $this->data = [];
        $this->rules = [];
        $this->fieldLabels = [];
        $this->validData = [];
    }

    public function setData($data){
        $this->reset();
        $this->data = $data;
    }

    public function setLabel($label,$name){
        $this->fieldLabels[$label] = $name;
        return $this;
    }

    public function errors(){
        return $this->errors;
    }

    public function firstOfAll(){
        $allErrors = [];
        foreach($this->errors as $field => $errors){
            if(count($errors) >= 1){
                $allErrors[$field] = current($errors);
            }
        }
        return $allErrors;
    }

    public function error($name,$allErrors = false){
        if($allErrors){
            return Arr::get($this->errors,$name);
        }else{
            return current($this->errors[$name]);
        }
    }


}