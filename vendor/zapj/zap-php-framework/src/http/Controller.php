<?php

namespace zap\http;

abstract class Controller
{
    protected array $params = [];

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