<?php

namespace zap\http;

abstract class Controller
{
    protected $params = [];

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param  array  $params
     */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }


}