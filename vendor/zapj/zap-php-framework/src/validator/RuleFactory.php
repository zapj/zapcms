<?php

namespace zap\validator;

use Exception;
use ReflectionException;
use ReflectionClass;
use zap\traits\SingletonTrait;

class RuleFactory
{
    use SingletonTrait;

    private $instanceRules = [];

    private $rulesNamespaces = ['zap\\validator\\rules'];

    public function addNamespace($namespace){
        $this->rulesNamespaces[] = $namespace;
        return $this;
    }

    /**
     * @param $ruleName
     *
     * @return \zap\validator\AbstractRule
     * @throws \Exception
     */
    public function make($ruleName,$validator){
        if(isset($this->instanceRules[$ruleName])){
            return $this->instanceRules[$ruleName];
        }
        foreach ($this->rulesNamespaces as $namespace) {
            try {
                $name = $namespace . '\\' . ucfirst(str_replace(' ','',ucwords(str_replace(['-','_'],' ',$ruleName))));
                $this->instanceRules[$ruleName] = $this->createRule($name,['validator'=>$validator]);
            } catch (ReflectionException $exception) {
                continue;
            }
        }
        return $this->instanceRules[$ruleName];
    }

    private function createRule($ruleName, $args = [])
    {
        $reflection = new ReflectionClass($ruleName);
        if (!$reflection->isSubclassOf(AbstractRule::class)) {
            throw new Exception("{$ruleName} must extends AbstructRule");
        }

        if (!$reflection->isInstantiable()) {
            throw new Exception(sprintf('"%s" must be instantiable', $ruleName));
        }

        return $reflection->newInstanceArgs($args);
    }



}