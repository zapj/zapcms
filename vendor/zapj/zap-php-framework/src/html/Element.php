<?php
/*
 * Copyright (c) 2025.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2025/4/22 16:01
 * @lastModified 2025/4/22 16:01
 *
 */

namespace zap\html;

class Element
{

    public string $tag;
    public $html;
    public $attributes;
    public $children;


    public function __construct($tag, $attributes = [], $children = []) {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    public function getTag(): string {
        return $this->tag;
    }

    public function setTag(string $tag): void {
        $this->tag = $tag;
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void {
        $this->attributes = $attributes;
    }

    public function getChildren(): array {
        return $this->children;
    }

    public function setChildren(array $children): void {
        $this->children = $children;
    }

    public function addChild(Element $child): void {
        $this->children[] = $child;
    }

    /**
     * @param string|null|Element $html
     */
    public function html($html): Element
    {
        $this->html = $html;
        return $this;
    }

    public function __toString() {
        $element = '<' . $this->tag;
        if($this->attributes) {
            foreach ($this->attributes as $name => $value) {
                $element .= ' ' . $name . '="' . $value . '"';
            }
        }
        if($this->html){
            $element .= '>' . $this->html . '</' . $this->tag . '>';
        }else if(in_array($this->tag,['a','li','ul','ol','h1','h2','h3','h4','h5','h6'])) {
            $element .= '></' . $this->tag . '>';
        } else if($this->tag == 'form'){
            $element .= '>';
        }else{
            $element .= '/>';
        }
        return $element;
    }
}