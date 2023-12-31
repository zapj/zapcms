<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:28
 * @lastModified 2023/12/27 上午11:21
 *
 */

namespace zap\cms;

use zap\traits\SingletonTrait;

class BreadCrumb
{
    use SingletonTrait;

    protected $breadCrumbs = [];



    function add($title,$href = "#",$isActive = null){
        $this->breadCrumbs[] = [
            'title'=>$title,
            'href'=>$href,
            'isActive' => $isActive
        ];
        return $this;
    }

    function display($start = null,$end = null){
        echo <<<EOF
<nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
  <ol class="breadcrumb mb-0">
EOF;
        echo $start;
        foreach ($this->breadCrumbs as $crumb){
            $ariaCurrent = $crumb['isActive'] == null ?: 'aria-current="page"';
            $linkClass = $crumb['isActive'] == null ?: 'text-secondary';
            echo "<li class=\"breadcrumb-item\" {$ariaCurrent}><a href=\"{$crumb['href']}\" class='{$linkClass}'>{$crumb['title']}</a></li>";
        }
        echo $end,'</ol></nav>';
    }
}