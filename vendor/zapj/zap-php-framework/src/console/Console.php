<?php

namespace zap\console;

use app\commands\User;
use zap\App;
use zap\DB;
use zap\util\Str;

class Console{
    protected App $app;
    protected Input $input;
    protected string $defaultCommand = '\zap\console\DefaultCommand';
    protected array $commands = [];
    public function __construct($appPath)
    {
        $this->app = new App($appPath);
        $this->input = new Input();
        set_exception_handler(null);
        set_error_handler(null);
        register_shutdown_function(function(){return false;});
    }

    public function setDefaultCommand($command): Console
    {
        $this->defaultCommand = $command;
        return $this;
    }

    public function addCommand($path,$namespace): Console
    {
        $this->commands[$path] = $namespace;
        return $this;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }


    public function execute() : int
    {
        $command = $this->input->getParam(1);
        if($command === null){
            return $this->callCommand($this->defaultCommand);
        }
        $namespace = '\\app\\commands\\';
        if(stristr($command,':') !== false){
           [$namespace,$command] = explode(':',$command);
            $namespace = "\\{$namespace}\\commands\\";
        }
        $command = $namespace . $command;
        return $this->callCommand($command);
    }

    protected function callCommand($command,$callDefault = false) : int
    {
        try{
            $reflect = new \ReflectionClass($command);
            if(!$reflect->isSubclassOf(Command::class)){
                exit("A {$command} does not extend the \\zap\\console\\Command");
            }
            $cmd = $reflect->newInstance();
            $cmd->setConsole($this);
            $cmd->setInput($this->input);
            $cmd->setOutput(new Output($this->input));
            $cmd->init();
            if($this->input->hasParam('h') || $this->input->hasParam('help')){
                return $cmd->help();
            }
            return $cmd->execute();
        }catch (\ReflectionException $e){
            if(!empty($this->defaultCommand) && $callDefault === false){
                return $this->callCommand($this->defaultCommand,true);
            }else{
                echo "Command Not found : {$command}";
            }
        }
        return Command::FAILURE;
    }
}