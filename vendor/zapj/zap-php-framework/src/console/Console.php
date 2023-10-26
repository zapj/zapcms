<?php

namespace zap\console;

use app\commands\User;
use zap\App;
use zap\DB;
use zap\util\Str;

class Console{
    protected $app;
    protected $input;
    public function __construct($appPath)
    {
        $this->app = new App($appPath);
        $this->input = new Args();
        set_exception_handler(null);
        set_error_handler(null);
        register_shutdown_function(function(){return false;});
    }


    public function execute() : int
    {
        $command = $this->input->getParam(1);
        if($command === null){
            return 0;
        }
        $namespace = '\\app\\commands\\';
        if(stristr($command,':') !== false){
           [$namespace,$command] = explode(':',$command);
            $namespace = "\\{$namespace}\\commands\\";
        }
        $command = $namespace . $command;
        try{
            $reflact = new \ReflectionClass($command);
            if(!$reflact->isSubclassOf(Command::class)){
                exit("A {$command} does not extend the \\zap\\console\\Command");
            }
            $cmd = $reflact->newInstance();
            $cmd->init();
            return $cmd->execute($this->input,new Output());
        }catch (\ReflectionException $e){
            echo "Command Not found : {$command}";
        }
        return 0;
    }
}