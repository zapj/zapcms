<?php
/*
 * Copyright (c) 2023-2026.  ZAP.CN  - ZAP CMS
 * Theme: Basic - 商务简约模板
 *
 * functions.php — 前台 & 后台共用函数
 * admin/functions.php — 仅后台加载（使用 AdminHook 注入 UI）
 */

// Frontend
if(defined('IN_ZAPCMS') && !defined('IN_ZAPCMS_ADMIN')){
    
    // 获取主题 URL
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

// 前后端共用的函数放这里（不加 IN_ZAPCMS_ADMIN 判断即可）
// if(defined('IN_ZAPCMS')){
//     function theme_shared_helper() { ... }
// }
