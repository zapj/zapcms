<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app;

class ZapQuery
{
    protected $condition;
    public function __construct($condition)
    {
        $this->condition = $condition;
    }


}