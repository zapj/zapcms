<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:28
 * @lastModified 2023/11/2 下午5:27
 *
 */

namespace zap\cms\twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class PageTwigExtension extends AbstractExtension implements GlobalsInterface
{
    public function getFunctions()
    {
        return [

        ];
    }


    public function getGlobals(): array
    {
        return [
            'pageState'=>pageState()
        ];
    }
}