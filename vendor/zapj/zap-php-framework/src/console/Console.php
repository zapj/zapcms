<?php

namespace zap\console;

use zap\App;
use zap\DB;

class Console{
    protected $app;
    protected $input;
    public function __construct($appPath)
    {
        $this->app = new App($appPath);
        $this->input = new Args();
    }


    public function execute() : int
    {
        print_r(DB::query("SELECT VERSION()")->fetch());
        print_r($this->input->getArgv());
        return 0;
    }
}