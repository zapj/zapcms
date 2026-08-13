<?php
// 注意：维护模式、调试、日志、后台前缀等运行参数已迁移到数据库 options 表（server.*），
// 请勿在此文件中写入这些配置，以免与后台“基础设置 > 服务器”冲突。
return [

    // Theme
    "theme" => "basic",

    //url
    'suffix'=>".html",

    // i18N
    "fallback_locale"=>"zh-CN",
    "available_languages" => [],

    //secure
    "app_key" => ""
];
