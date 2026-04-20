<?php
/*
 * Copyright (c) 2023-2026.  ZAP.CN  - ZAP CMS
 * Theme: Basic - 商务简约模板
 */

// Frontend
if(defined('IN_ZAP_CMS')){
    
    // 获取主题URL
    function themes_url_basic($path = '') {
        return base_url('/themes/basic/' . ltrim($path, '/'));
    }
    
    // 格式化日期
    function format_date($date, $format = 'Y-m-d') {
        return date($format, strtotime($date));
    }
    
    // 截断文本
    function truncate_text($text, $length = 100, $suffix = '...') {
        $text = strip_tags($text);
        if (mb_strlen($text) > $length) {
            return mb_substr($text, 0, $length) . $suffix;
        }
        return $text;
    }
}
