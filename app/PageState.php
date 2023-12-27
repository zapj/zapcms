<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:30
 * @lastModified 2023/12/25 下午3:53
 *
 */

namespace app;

use ArrayObject;
use zap\cms\Catalog;

class PageState extends \ArrayObject
{
    public $isHome;
    public $nodeId;
    public $node;
    public $tags;
    public $tag;
    public $catalog;
    public ?array $catalogList;
    public $catalogPaths;

    public $options = [];
    public $isCatalog = false;
    public $isNode = false;
    public $nodeType;
    public $nodeMimeType;

    public function __construct()
    {
        parent::__construct([], ArrayObject::STD_PROP_LIST|ArrayObject::ARRAY_AS_PROPS, "ArrayIterator");
    }

    public function addOptions($options){
        $this->options += $options;
    }

    public function getCatalog($key = null){
        if(!$this->catalog && $this->nodeType == 'catalog'){
            $this->catalog = Catalog::instance()->get($this->nodeId);
        }
        return $key ? $this->catalog[$key] : $this->catalog;
    }

    public function getCatalogList(): ?array
    {
        if(!$this->catalogList){
            $this->catalogList = \zap\facades\Cache::get('top_menu',function(){
                return \zap\cms\Catalog::instance()->getTreeArray();
            },5000);
        }
        return $this->catalogList;
    }




}