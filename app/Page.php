<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app;

use zap\Catalog;

class Page
{
    public $isHome;
    public $nodeId;
    public $node;
    public $tags;
    public $tag;
    public $catalog;
    public $catalogList;
    public $catalogPaths;

    public $options = [];
    public $isCatalog = false;
    public $isNode = false;
    public $nodeType;
    public $nodeMimeType;


    public function addOptions($options){
        $this->options += $options;
    }

    public function getCatalog($key = null){
        if(!$this->catalog && $this->nodeType == 'catalog'){
            $this->catalog = Catalog::instance()->get($this->nodeId);
        }
        return $key ? $this->catalog[$key] : $this->catalog;
    }

    public function getCatalogList(){
        if(!$this->catalogList){
            $this->catalogList = \zap\facades\Cache::get('top_menu',function(){
                return \zap\Catalog::instance()->getTreeArray();
            },5000);
        }
        return $this->catalogList;
    }

}