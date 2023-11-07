<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap;

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