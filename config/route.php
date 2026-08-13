<?php

/*
|-----------------------------------------
| ZAP CMS 路由配置
|-----------------------------------------
| $router 由 App::dispatchRoutes() 在 require 之前创建并传入作用域
|
| 路由注册顺序很重要：
|   1. 后台管理路由（优先匹配）
|   2. 前台 CMS 路由（fallback，兜底所有未被后台匹配的请求）
*/
/**
 * @var \zap\http\Router $router
 */
// 定义后台前缀（保存在 options 表 server.admin_prefix，可在后台“基础设置 > 服务器”修改）
if (!defined('Z_ADMIN_PREFIX')) {
    define('Z_ADMIN_PREFIX', get_option('server.admin_prefix', 'z-admin'));
}

// ──────────────────── 后台路由 ────────────────────
$adminBootstrap = new \zapcms\Bootstrap();
$adminBootstrap->handle($router);

// ──────────────────── 前台 CMS 路由 ────────────────────
// 作为最后的 fallback 处理器，匹配所有未被后台拦截的请求
$startup = new \app\Startup();
$startup->handle($router);
