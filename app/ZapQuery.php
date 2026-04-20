<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:09
 * @lastModified 2023/10/26 上午10:16
 *
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