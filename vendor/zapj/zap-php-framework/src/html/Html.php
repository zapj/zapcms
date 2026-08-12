<?php

namespace zap\html;

/**
 * HTML 元素工厂 — 流式 Builder
 *
 * <code>
 * // 纯流式
 * echo Html::el('div')->class('card')->id('app')->text('Hello');
 *
 * // 带属性构造
 * echo Html::el('div', '内容', ['class' => 'box']);
 *
 * // 子节点嵌套
 * echo Html::el('ul')->append(
 *     Html::el('li')->text('项目1'),
 *     Html::el('li')->text('项目2'),
 * );
 *
 * // 便捷方法
 * echo Html::a('/home', '首页')->class('nav-link');
 * echo Html::img('/logo.png')->class('logo')->attr('alt', 'Logo');
 * echo Html::input('text', 'username', '')->id('input-user')->class('form-control');
 * echo Html::form('/login')->class('form')->append(
 *     Html::input('email', 'email')->class('form-input'),
 *     Html::input('submit')->attr('value', '登录'),
 * );
 * </code>
 */
class Html
{
    /**
     * 创建元素（无内容，纯属性）
     */
    public static function create(string $tagName, array $attributes = [], array $children = []): Element
    {
        return new Element($tagName, $attributes, $children);
    }

    /**
     * 创建有内容的元素
     * @param string|\Stringable|null $html  元素内容（传 null 跳过）
     */
    public static function el(string $tagName, $html = null, array $attributes = [], array $children = []): Element
    {
        $el = new Element($tagName, $attributes, $children);
        if ($html !== null) {
            $el->html($html);
        }
        return $el;
    }

    // ========== Doctype ==========

    /**
     * 生成文档类型声明（DOCTYPE）
     *
     * @param string $type 类型：html5（默认）/ html4-strict / html4-trans / html4-frameset
     *                     / xhtml1-strict / xhtml1-trans / xhtml1-frameset / xhtml11
     * @return string 如 "<!DOCTYPE html>"
     *
     * @example
     * <code>
     * echo Html::doctype();              // <!DOCTYPE html>
     * echo Html::doctype('xhtml1-strict'); // <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" ...>
     * </code>
     */
    public static function doctype(string $type = 'html5'): string
    {
        static $doctypes = [
            'html5'           => '<!DOCTYPE html>',
            'html4-strict'    => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
            'html4-trans'     => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">',
            'html4-frameset'  => '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">',
            'xhtml1-strict'   => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">',
            'xhtml1-trans'    => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
            'xhtml1-frameset' => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">',
            'xhtml11'         => '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">',
        ];

        return $doctypes[strtolower($type)] ?? $doctypes['html5'];
    }

    // ========== Convenience Methods ==========

    /** &lt;a href="..."&gt;content&lt;/a&gt; */
    public static function a(string $href, $content = null, array $attributes = []): Element
    {
        return new Element('a', [...$attributes, 'href' => $href]);
    }

    /** &lt;img src="..."&gt; */
    public static function img(string $src, array $attributes = []): Element
    {
        return new Element('img', [...$attributes, 'src' => $src]);
    }

    /** &lt;div&gt; */
    public static function div($content = null, array $attributes = []): Element
    {
        return static::el('div', $content, $attributes);
    }

    /** &lt;span&gt; */
    public static function span($content = null, array $attributes = []): Element
    {
        return static::el('span', $content, $attributes);
    }

    /** &lt;input type="..."&gt; */
    public static function input(string $type = 'text', string $name = '', string $value = '', array $attributes = []): Element
    {
        $attrs = $attributes;
        if ($name !== '') {
            $attrs['name'] = $name;
        }
        if ($value !== '') {
            $attrs['value'] = $value;
        }
        return new Element('input', ['type' => $type, ...$attrs]);
    }

    /** &lt;textarea&gt; */
    public static function textarea(string $name, string $value = '', array $attributes = []): Element
    {
        return static::el('textarea', $value, [...$attributes, 'name' => $name]);
    }

    /** &lt;select&gt; */
    public static function select(array $options = [], $selected = null, array $attributes = []): Element
    {
        $el = new Element('select', $attributes);
        foreach ($options as $val => $label) {
            $attrs = ['value' => $val];
            if ((string) $val === (string) $selected) {
                $attrs['selected'] = true;
            }
            $el->append(new Element('option', $attrs, [(string) $label]));
        }
        return $el;
    }

    /** &lt;option&gt; */
    public static function option(string $value, string $label = '', bool $selected = false, array $attributes = []): Element
    {
        $attrs = ['value' => $value];
        if ($selected) {
            $attrs['selected'] = true;
        }
        return static::el('option', $label ?: $value, [...$attrs, ...$attributes]);
    }

    /** &lt;form action="..." method="..."&gt; */
    public static function form(string $action = '', string $method = 'POST', array $attributes = []): Element
    {
        return new Element('form', [
            'action' => $action,
            'method' => $method,
            ...$attributes,
        ]);
    }

    /** &lt;label&gt; */
    public static function label(string $for, $content = null, array $attributes = []): Element
    {
        return static::el('label', $content, [...$attributes, 'for' => $for]);
    }

    /** &lt;button&gt; */
    public static function button($content = null, string $type = 'submit', array $attributes = []): Element
    {
        return static::el('button', $content, ['type' => $type, ...$attributes]);
    }

    /** &lt;script&gt; */
    public static function script(string $src = '', string $content = '', array $attributes = []): Element
    {
        $attrs = $attributes;
        if ($src) {
            $attrs['src'] = $src;
        }
        return static::el('script', $content, $attrs);
    }

    /** &lt;link&gt; */
    public static function link(string $href, string $rel = 'stylesheet', array $attributes = []): Element
    {
        return new Element('link', ['href' => $href, 'rel' => $rel, ...$attributes]);
    }

    /** &lt;meta&gt; */
    public static function meta(string $name, string $content, array $attributes = []): Element
    {
        return new Element('meta', ['name' => $name, 'content' => $content, ...$attributes]);
    }

    /** &lt;br&gt; */
    public static function br(): Element
    {
        return new Element('br');
    }

    /** &lt;hr&gt; */
    public static function hr(array $attributes = []): Element
    {
        return new Element('hr', $attributes);
    }

    /** &lt;ul&gt; */
    public static function ul(array $items = [], array $attributes = []): Element
    {
        $el = new Element('ul', $attributes);
        foreach ($items as $item) {
            $el->append(is_string($item) ? static::el('li', $item) : $item);
        }
        return $el;
    }

    /** &lt;ol&gt; */
    public static function ol(array $items = [], array $attributes = []): Element
    {
        $el = new Element('ol', $attributes);
        foreach ($items as $item) {
            $el->append(is_string($item) ? static::el('li', $item) : $item);
        }
        return $el;
    }

    /** &lt;p&gt; */
    public static function p($content = null, array $attributes = []): Element
    {
        return static::el('p', $content, $attributes);
    }

    /** &lt;h1&gt; ～ &lt;h6&gt; */
    public static function h(int $level, $content = null, array $attributes = []): Element
    {
        $tag = 'h' . max(1, min(6, $level));
        return static::el($tag, $content, $attributes);
    }

    /** &lt;table&gt; */
    public static function table(array $rows = [], array $headers = [], array $attributes = []): Element
    {
        $el = new Element('table', $attributes);
        if ($headers) {
            $thead = new Element('thead');
            $tr = new Element('tr');
            foreach ($headers as $h) {
                $tr->append(static::el('th', $h));
            }
            $el->append($thead->append($tr));
        }
        if ($rows) {
            $tbody = new Element('tbody');
            foreach ($rows as $row) {
                $tr = new Element('tr');
                foreach ((array) $row as $cell) {
                    $tr->append(static::el('td', $cell));
                }
                $tbody->append($tr);
            }
            $el->append($tbody);
        }
        return $el;
    }

    // ========== Deprecated ==========

    /** @deprecated 使用 Html::el('form', ...)->render() 替代手动关闭 */
    public static function form_close(): string
    {
        return '</form>';
    }
}
