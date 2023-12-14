<?php

namespace zap\console;

class Command{
    const SUCCESS = 0;
    const FAILURE = 1;
    protected Input $input;
    protected Output $out;
    protected Console $console;

    public function setConsole($console): void
    {
        $this->console = $console;
    }
    public function getInput(): Input
    {
        return $this->input;
    }

    public function setInput(Input $input): void
    {
        $this->input = $input;
    }

    public function getOutput(): Output
    {
        return $this->out;
    }

    public function setOutput(Output $out): void
    {
        $this->out = $out;
    }

    function init() :void{

    }

    public function description() : string
    {
        return '';
    }

    function help() :int{
        return self::SUCCESS;
    }


    function execute() : int
    {
        return self::SUCCESS;
    }
}