<?php

namespace zap\console;

use zap\util\FileUtils;

class DefaultCommand extends Command
{
    function execute(): int
    {
        $this->out->writeln("可用命令列表");
        $commands = $this->console->getCommands();
        $commands = empty($commands) ? ['app'] : $commands;
//        $commandList = [];
        foreach ($commands as $path=>$prefix){
            $dir = base_path($path);
            if(!is_dir($dir)){
                continue;
            }
            $f = new \FilesystemIterator($dir);
            while($f->valid()){
                if($f->getExtension() === 'php'){
                    $baseName = $f->getBasename('.php');
                    $command = "\\{$prefix}\\commands\\" . $baseName;
                    if(class_exists($command)){
                        $cmd = new $command();
                        if(method_exists($cmd,'description')){

                            printf( "  %-25s\t%s\r\n",($prefix==='app'?'':$prefix . ':').  "{$baseName}",$cmd->description());
                        }
                    }
                }
                $f->next();
            }
        }
        echo str_repeat('-',50),PHP_EOL;
        echo "Example of Call",PHP_EOL;
        echo "php bin/console zap:Command arg1 arg2 -a -b",PHP_EOL;
        return self::SUCCESS;
    }


}