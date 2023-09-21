<?php

namespace zap\console;

class Args {
    private $argv;

    public function __construct($argv = null)
    {
        $argv = $_SERVER['argv'] ?? [];
        array_shift($argv);
        $this->argv = $argv;
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