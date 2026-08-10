<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:46
 * @lastModified 2023/12/14 上午10:43
 *
 */

namespace zap\cms;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlXss
{
    public static function clean($html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
}