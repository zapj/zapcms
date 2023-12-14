<?php

namespace zap\console;

use zap\util\Str;

class Input {
    private $argv;
    protected array $params = [];

    public function __construct($argv = null)
    {
        $argv = $_SERVER['argv'] ?? [];
        array_shift($argv);
        $this->argv = $argv ?? [];
        $this->parseArgs();
    }

    protected function parseArgs(): void
    {
        $argv = $this->argv;
        while($current = array_shift($argv)){
            if(Str::startsWith($current,'-')){
                $next = array_shift($argv);
                $nextIsFlag = Str::startsWith($next,'-');
                if($nextIsFlag){
                    $this->params[trim($current,' -')] = true;
                    array_unshift($argv,$next);
                }else{
                    $this->params[trim($current,' -')] = $next ?? true;
                }
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
     * @param array $argv
     */
    public function setArgv(array $argv): void
    {
        $this->argv = $argv;
    }

    /**
     * @return array
     */
    public function getArgv(): array
    {
        return $this->argv;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }


}