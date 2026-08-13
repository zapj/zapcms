<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 *
 * 模块 autoload 脚本示例：
 * 模块注册（写入 options 表）后，后台每次启动都会加载本文件，
 * 可在此注册 hooks、过滤器或扩展后台功能。
 */

defined('IN_ZAPCMS_ADMIN') or die('No permission to access');

// 示例：注册后台 hook（需 ZapCMS >= 1.0）
// \zapcms\AdminHook::on('admin_menu_after', function () {
//     echo '<li><a href="' . site_url('/z-admin/mod/shop/settings') . '">商城设置</a></li>';
// });
