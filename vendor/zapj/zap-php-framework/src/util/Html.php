<?php

namespace zap\util;

use zap\traits\SingletonTrait;

/**
 * HTML 工具类
 */
class Html
{
    use SingletonTrait;

    // ========== 编解码 ==========

    public static function decode(string $text): string
    {
        return html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    }

    public static function encode(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // ========== HTML 片段 ==========

    public static function doctype(string $type = 'html5'): string
    {
        switch ($type) {
            case 'html4-strict':
                return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
            case 'html4-trans':
                return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
            default:
                return '<!DOCTYPE html>';
        }
    }

    /** @deprecated 使用 doctype() */
    public function docType(string $type = 'html5'): string
    {
        return self::doctype($type);
    }

    public static function br(): string
    {
        return '<br>';
    }

    /**
     * 生成 style 标签
     */
    public static function style(string $data, bool $newline = false): string
    {
        $nl = $newline ? "\n" : '';
        return "<style>{$nl}{$data}{$nl}</style>";
    }

    /**
     * 生成 HTML 标签
     */
    public static function tag(string $tag, ?string $content = null, array $options = []): string
    {
        $attr = self::buildAttributes($options);
        if ($content === null) {
            return "<{$tag}{$attr}>";
        }
        return "<{$tag}{$attr}>{$content}</{$tag}>";
    }

    /**
     * 生成链接 &lt;a&gt;
     */
    public static function a(string $url, string $text = '', array $options = []): string
    {
        $options['href'] = $url;
        return self::tag('a', $text ?: htmlspecialchars($url), $options);
    }

    /**
     * 生成图片 &lt;img&gt;
     */
    public static function img(string $src, array $options = []): string
    {
        $options['src'] = $src;
        if (!isset($options['alt'])) {
            $options['alt'] = '';
        }
        return self::tag('img', null, $options);
    }

    /**
     * 生成 &lt;meta&gt; 标签
     */
    public static function meta(string $name, string $content): string
    {
        return "<meta name=\"{$name}\" content=\"{$content}\">";
    }

    /**
     * 生成可排序列表
     * @param array<int|string, string> $items
     */
    public static function ul(array $items, array $options = []): string
    {
        $html = '';
        foreach ($items as $key => $item) {
            $html .= self::tag('li', $item);
        }
        return self::tag('ul', $html, $options);
    }

    /**
     * 生成有序列表
     * @param array<int|string, string> $items
     */
    public static function ol(array $items, array $options = []): string
    {
        $html = '';
        foreach ($items as $key => $item) {
            $html .= self::tag('li', $item);
        }
        return self::tag('ol', $html, $options);
    }

    /**
     * 生成表单 &lt;form&gt;
     */
    public static function form(string $action = '', string $method = 'POST', array $options = []): string
    {
        $options['action'] = $action;
        $options['method'] = $method;
        return self::tag('form', '', $options);
    }

    /**
     * 生成 &lt;input&gt;
     */
    public static function input(string $type, string $name, string $value = '', array $options = []): string
    {
        $options['type'] = $type;
        $options['name'] = $name;
        $options['value'] = $value;
        return self::tag('input', null, $options);
    }

    /**
     * 生成 &lt;script&gt; 标签
     */
    public static function script(string $src = '', string $content = '', array $options = []): string
    {
        if ($src) {
            $options['src'] = $src;
        }
        return self::tag('script', $content, $options);
    }

    /**
     * 生成 &lt;link&gt; 标签
     */
    public static function link(string $href, string $rel = 'stylesheet', array $options = []): string
    {
        $options['href'] = $href;
        $options['rel'] = $rel;
        return self::tag('link', null, $options);
    }

    // ========== 辅助 ==========

    /**
     * 将属性数组转为 HTML 属性字符串
     */
    public static function buildAttributes(array $attributes): string
    {
        if (empty($attributes)) {
            return '';
        }
        $parts = [];
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                continue;
            }
            if ($value === true) {
                $parts[] = $key;
            } else {
                $parts[] = $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES) . '"';
            }
        }
        return ' ' . implode(' ', $parts);
    }

    /**
     * 将普通文本转为 HTML 段落（nl2br + 段落化）
     */
    public static function paragraph(string $text): string
    {
        $paragraphs = preg_split('/\n\s*\n/', trim($text));
        $result = '';
        foreach ($paragraphs as $p) {
            $result .= self::tag('p', nl2br(self::encode($p)));
        }
        return $result;
    }

    // ========== 兼容旧接口 ==========

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::instance(), $name], $arguments);
    }
}
