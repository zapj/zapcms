<?php

namespace zap\validator;

use zap\http\Request;
use zap\util\Arr;

class Validator
{
    /** @var array 验证规则 [{ruleName} => [{field} => params]] */
    protected $rules = [];

    /** @var bool 遇到第一个错误停止检测 */
    protected $stopFirstFail = false;

    /** @var array 待验证数据 */
    public $data;

    /** @var array 错误信息 */
    protected $errors = [];

    /** @var array 字段标签映射 */
    protected $fieldLabels = [];

    /** @var array 验证通过的数据 */
    protected $validData = [];

    /** @var array 自定义错误消息 [field.rule => message] */
    protected $customMessages = [];

    /** @var array 需要 bail 的字段列表 */
    protected $bailFields = [];

    /** @var array 可空字段列表（跳过除 Required 以外的规则） */
    protected $nullableFields = [];

    /** @var array 验证后回调 */
    protected $afterCallbacks = [];

    /** @var array 隐式规则（即使字段不存在也要执行的规则） */
    protected static $implicitRules = [
        'required', 'required_with',
    ];

    /**
     * 静态工厂方法
     *
     * @param array|null $data
     * @return static
     */
    public static function make($data = null)
    {
        return new static($data);
    }

    public function __construct($data = null)
    {
        if ($data === null) {
            $this->data = Request::method() === 'GET' ? $_GET : $_POST;
        } else {
            $this->data = $data;
        }
    }

    /**
     * 添加验证规则
     *
     * @param string $ruleName  规则名
     * @param string|array $fields  字段名或字段名数组
     * @param mixed $params  规则参数
     * @return $this
     */
    public function rule($ruleName, $fields = [], $params = [])
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }
        foreach ($fields as $field) {
            $this->rules[$ruleName][$field] = $params;
        }
        return $this;
    }

    /**
     * 设置自定义错误消息
     *
     * @param array $messages  ['field.rule' => 'message', ...]
     * @return $this
     */
    public function messages(array $messages)
    {
        $this->customMessages = array_merge($this->customMessages, $messages);
        return $this;
    }

    /**
     * 指定字段遇到第一个错误后停止后续规则验证
     *
     * @param string|array $fields
     * @return $this
     */
    public function bail($fields)
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }
        $this->bailFields = array_merge($this->bailFields, $fields);
        return $this;
    }

    /**
     * 指定字段可为空（为空时跳过除 Required 以外的规则）
     *
     * @param string|array $fields
     * @return $this
     */
    public function nullable($fields)
    {
        if (is_string($fields)) {
            $fields = [$fields];
        }
        $this->nullableFields = array_merge($this->nullableFields, $fields);
        return $this;
    }

    /**
     * 设置全局遇到第一个错误停止
     *
     * @param bool $stop
     * @return $this
     */
    public function stopOnFirstFailure($stop = true)
    {
        $this->stopFirstFail = $stop;
        return $this;
    }

    /**
     * 添加命名空间以支持自定义规则
     *
     * @param string $namespace
     * @return $this
     */
    public function addNamespace($namespace)
    {
        RuleFactory::instance()->addNamespace($namespace);
        return $this;
    }

    /**
     * 添加验证后回调
     *
     * @param callable $callback  function(Validator $validator)
     * @return $this
     */
    public function after(callable $callback)
    {
        $this->afterCallbacks[] = $callback;
        return $this;
    }

    /**
     * 执行验证
     *
     * @return bool
     */
    public function validate()
    {
        $this->errors = [];
        $this->validData = [];

        foreach ($this->rules as $ruleName => $rule) {
            $r = RuleFactory::instance()->make($ruleName, $this);

            foreach ($rule as $field => $params) {
                $r->setParams($params);

                // 隐式规则（如 Required）即使字段不存在也要执行
                $isImplicit = in_array($ruleName, self::$implicitRules, true);
                $value = $this->getValue($this->data, $field);

                // 可空字段：值为空且非隐式规则时跳过
                if (!$isImplicit && $this->isNullableField($field) && $this->isValueEmpty($value)) {
                    continue;
                }

                $ret = $r->validate($field, $value);

                if (!$ret) {
                    $message = $this->resolveMessage($field, $ruleName, $r);
                    $this->addError($field, $ruleName, $value, $message, $r->translateParams());

                    if ($this->stopFirstFail) {
                        break 2;
                    }

                    // 字段级 bail：该字段后续规则跳过
                    if (in_array($field, $this->bailFields, true)) {
                        continue 2;
                    }
                } else {
                    $this->validData[$field] = $value;
                }
            }
        }

        // 执行 after 回调
        foreach ($this->afterCallbacks as $callback) {
            $callback($this);
        }

        return empty($this->errors);
    }

    /**
     * 解析错误消息（优先使用自定义消息）
     */
    protected function resolveMessage($field, $ruleName, AbstractRule $rule)
    {
        $key = $field . '.' . $ruleName;

        if (isset($this->customMessages[$key])) {
            return $this->customMessages[$key];
        }

        return 'validator.' . $rule->translateMsgKey();
    }

    /**
     * 判断值是否为空
     */
    protected function isValueEmpty($value)
    {
        if (is_null($value)) {
            return true;
        }
        if (is_string($value) && trim($value) === '') {
            return true;
        }
        if (is_array($value) && empty($value)) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否为 nullable 字段
     */
    protected function isNullableField($field)
    {
        // 支持通配符: items.* 匹配 items.0
        foreach ($this->nullableFields as $pattern) {
            if ($field === $pattern) {
                return true;
            }
            if (str_contains($pattern, '*') && fnmatch($pattern, $field)) {
                return true;
            }
        }
        return false;
    }

    public function getValidData()
    {
        return $this->validData;
    }

    public function get($name, $default = null)
    {
        return Arr::get($this->validData, $name, $default);
    }

    /**
     * 从嵌套数据中提取字段值，支持点号分隔和通配符
     */
    public function getValue($data, $field)
    {
        $parent_is_wildcard = false;
        foreach (explode('.', $field) as $segment) {
            if (!is_array($data)) {
                return null;
            }

            if ($parent_is_wildcard) {
                $values = [];
                foreach ($data as $val) {
                    $values[] = $val[$segment] ?? null;
                }
                $data = $values;
                $parent_is_wildcard = false;
                continue;
            }

            if ($segment === '*') {
                $parent_is_wildcard = true;
                $values = [];
                foreach ($data as $val) {
                    $values[] = $val;
                }
                $data = $values;
                continue;
            }

            if (is_numeric($segment)) {
                $segment = (int) $segment;
            }

            if (!array_key_exists($segment, $data)) {
                return null;
            }

            $data = $data[$segment];
        }
        return $data;
    }

    /**
     * 添加错误信息
     */
    public function addError($field, $rule, $value, $message, $params = [])
    {
        if (!is_array($params)) {
            $params = ['param' => $params];
        }
        $params['value'] = $value;
        $params['field'] = $this->fieldLabels[$field] ?? '';

        // 支持直接使用自定义消息（非 trans key）
        if (isset($this->customMessages[$field . '.' . $rule])) {
            $this->errors[$field][$rule] = $this->formatMessage($message, $params);
        } else {
            $this->errors[$field][$rule] = trans($message, $params);
        }

        return $this;
    }

    /**
     * 格式化自定义消息中的占位符
     */
    protected function formatMessage($message, $params)
    {
        $replace = [];
        foreach ($params as $key => $value) {
            $replace['{' . $key . '}'] = is_array($value) ? implode(', ', $value) : (string) $value;
        }
        return strtr($message, $replace);
    }

    /**
     * 设置字段标签（批量或单个）
     *
     * @param string|array $field  字段名或 [字段名 => 标签] 数组
     * @param string|null $title  标签文本
     * @return $this
     */
    public function setLabels($field, $title = null)
    {
        if (is_array($field)) {
            $this->fieldLabels = array_merge($this->fieldLabels, $field);
        } else {
            $this->fieldLabels[$field] = $title;
        }
        return $this;
    }

    /**
     * @deprecated 使用 setLabels() 代替
     */
    public function setLabel($label, $name)
    {
        $this->fieldLabels[$label] = $name;
        return $this;
    }

    /**
     * 重置验证器状态
     */
    public function reset()
    {
        $this->data = [];
        $this->rules = [];
        $this->errors = [];
        $this->fieldLabels = [];
        $this->validData = [];
        $this->bailFields = [];
        $this->nullableFields = [];
        $this->afterCallbacks = [];
        $this->customMessages = [];
    }

    /**
     * 设置数据并重置状态
     */
    public function setData($data)
    {
        $this->reset();
        $this->data = $data;
    }

    /**
     * 获取所有错误
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * 获取每个字段的第一个错误
     */
    public function firstOfAll()
    {
        $allErrors = [];
        foreach ($this->errors as $field => $errors) {
            if (!empty($errors)) {
                $allErrors[$field] = current($errors);
            }
        }
        return $allErrors;
    }

    /**
     * 获取指定字段的错误
     */
    public function error($name, $allErrors = false)
    {
        if (!isset($this->errors[$name])) {
            return null;
        }
        if ($allErrors) {
            return Arr::get($this->errors, $name);
        }
        return current($this->errors[$name]);
    }

    /**
     * 判断验证是否失败
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * 判断验证是否通过
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * 验证数据，失败时抛出异常
     *
     * @param array|null $data
     * @return array 验证通过的数据
     * @throws \RuntimeException
     */
    public function validated()
    {
        if ($this->fails()) {
            $first = $this->firstOfAll();
            throw new \RuntimeException('Validation failed: ' . implode('; ', $first));
        }
        return $this->validData;
    }
}
