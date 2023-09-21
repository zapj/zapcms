<?php

namespace zap\console;

class Command{
    const SUCCESS = 0;
    const FAILURE = 1;
    function init() :void{

    }
    function execute(Args $input,Output $output) : int
    {
        return self::SUCCESS;
    }
}