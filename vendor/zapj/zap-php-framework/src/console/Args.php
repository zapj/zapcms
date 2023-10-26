<?php

namespace zap\console;

use zap\util\Str;

class Args {
    private $argv;
    protected $params = [];

    public function __construct($argv = null)
    {
        $argv = $_SERVER['argv'] ?? [];
        array_shift($argv);
        $this->argv = $argv;
        $this->parseArgs();
    }

    protected function parseArgs(){
        $argv = $this->argv;
        while($current = array_shift($argv)){
            if(Str::startsWith($current,'-')){
                $next = array_shift($argv);
                $nextIsFlag = Str::startsWith($next,'-');
                $this->params[trim($current,' -')] = $nextIsFlag ? true : $next;
            }else{
                $this->params[] = $current;
            }
        }
    }

    public function getParam($name,$default = null){
        if(is_int($name)){
            return $this->params[($name-1)] ?? $default;
        }
        return $this->params[$name] ?? $default;
    }

    public function hasParam($name): bool
    {
        if(is_int($name)){
            return isset($this->params[($name-1)]) ;
        }
        return isset($this->params[$name]);
    }

    /**
     * @param array|mixed $argv
     */
    public function setArgv($argv): void
    {
        $this->argv = $argv;
    }

    /**
     * @return array|mixed
     */
    public function getArgv()
    {
        return $this->argv;
    }


}