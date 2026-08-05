<?php
/*
 * Copyright (c) 2025.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2025/4/22 16:01
 * @lastModified 2025/4/22 16:01
 */

namespace zap\html;

class Element implements \Stringable
{
    /** @var string[] Void elements — never have closing tags */
    private const VOID_ELEMENTS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];

    /** @var string[] Boolean attributes — rendered as presence-only */
    private const BOOLEAN_ATTRS = [
        'autofocus', 'checked', 'disabled', 'multiple', 'readonly',
        'required', 'selected', 'async', 'defer', 'novalidate',
    ];

    public string $tag;
    public string $html = '';
    public array $attributes = [];
    public array $children = [];

    public function __construct(string $tag, array $attributes = [], array $children = [])
    {
        $this->tag = $tag;
        $this->attributes = $attributes;
        $this->children = $children;
    }

    // ========== Fluent Attribute Methods ==========

    /**
     * 设置单个属性
     * @param mixed $value  true 渲染为布尔属性，null/false 则移除该属性
     */
    public function attr(string $name, $value = true)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /** 批量设置属性 */
    public function attrs(array $attrs)
    {
        foreach ($attrs as $k => $v) {
            $this->attributes[$k] = $v;
        }
        return $this;
    }

    /** 追加 CSS class（自动去重） */
    public function class(string $class)
    {
        $existing = $this->attributes['class'] ?? '';
        $classes = $existing ? array_merge(explode(' ', $existing), explode(' ', $class)) : explode(' ', $class);
        $this->attributes['class'] = implode(' ', array_unique(array_filter($classes)));
        return $this;
    }

    /** 设置 id */
    public function id(string $id)
    {
        $this->attributes['id'] = $id;
        return $this;
    }

    /** 设置内联 style */
    public function style(string $style)
    {
        $this->attributes['style'] = ($this->attributes['style'] ?? '') . $style;
        return $this;
    }

    /** 设置 data-* 属性 */
    public function data(string $name, $value)
    {
        $this->attributes['data-' . $name] = $value;
        return $this;
    }

    // ========== Content Methods ==========

    /**
     * 设置纯文本内容（自动 HTML 转义）
     */
    public function text(string $text)
    {
        $this->html = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        return $this;
    }

    /**
     * 设置原始 HTML 内容
     * @param string|Stringable $html
     */
    public function html($html)
    {
        $this->html = (string) $html;
        return $this;
    }

    // ========== Children Methods ==========

    /** 追加子节点 */
    public function append(...$children)
    {
        foreach ($children as $child) {
            $this->children[] = is_string($child) ? $child : $child;
        }
        return $this;
    }

    /** 前置子节点 */
    public function prepend(...$children)
    {
        $items = [];
        foreach ($children as $child) {
            $items[] = is_string($child) ? $child : $child;
        }
        array_unshift($this->children, ...$items);
        return $this;
    }

    // ========== Backward Compat: Getter/Setter ==========

    /** @deprecated 直接访问 $this->tag 属性 */
    public function getTag(): string
    {
        return $this->tag;
    }

    /** @deprecated 直接访问 $this->tag 属性 */
    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    /** @deprecated 直接访问 $this->attributes 属性 */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /** @deprecated 直接访问 $this->attributes 属性 */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /** @deprecated 直接访问 $this->children 属性 */
    public function getChildren(): array
    {
        return $this->children;
    }

    /** @deprecated 直接访问 $this->children 属性 */
    public function setChildren(array $children): void
    {
        $this->children = $children;
    }

    /** @deprecated 使用 append() 方法 */
    public function addChild(self $child): void
    {
        $this->children[] = $child;
    }

    // ========== Render ==========

    /** 渲染为 HTML 字符串 */
    public function render(): string
    {
        $attr = $this->buildAttributes();

        // Void 元素：无闭合标签
        if ($this->isVoid()) {
            return "<{$this->tag}{$attr}>";
        }

        // 普通元素：带闭合标签 + 内容/子节点
        $inner = $this->html ?: $this->renderChildren();
        return "<{$this->tag}{$attr}>{$inner}</{$this->tag}>";
    }

    /** 自动字符串化 */
    public function __toString(): string
    {
        return $this->render();
    }

    // ========== Internal ==========

    private function isVoid(): bool
    {
        return in_array($this->tag, self::VOID_ELEMENTS, true);
    }

    /** 构建 HTML 属性字符串 */
    private function buildAttributes(): string
    {
        if (empty($this->attributes)) {
            return '';
        }

        $parts = [];
        foreach ($this->attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            if ($value === true || in_array($key, self::BOOLEAN_ATTRS, true) && $value === true) {
                $parts[] = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            } else {
                $parts[] = htmlspecialchars($key, ENT_QUOTES, 'UTF-8')
                         . '="'
                         . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
                         . '"';
            }
        }
        return ' ' . implode(' ', $parts);
    }

    /** 渲染子节点 */
    private function renderChildren(): string
    {
        $out = '';
        foreach ($this->children as $child) {
            $out .= (string) $child;
        }
        return $out;
    }
}
