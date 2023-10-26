<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app;

class Page
{
    public $isHome;
    public $nodeId;
    public $tags;
    public $tag;
    public $catalog;

    public $options = [];

    public function addOptions($options){
        $this->options += $options;
    }

    public function getCatalog(){
        $this->catalog = \zap\facades\Cache::get('top_menu',function(){
            return \zap\Catalog::instance()->getTreeArray();
        },5000);
        return $this->catalog;
    }

}