<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:09
 * @lastModified 2023/12/22 下午12:02
 *
 */

const ZAP_CMS_VERSION = '1.0.2';
const ZAP_CMS_RELEASE_DATE = '2026-4-20';

if (PHP_VERSION_ID < 70400) {
    exit('ZAP CMS requires PHP 7.4.0+.');
}

/**
 * 检测 PDO 异常是否为"数据库表不存在"引起。
 * 若检测到表不存在，跳转到安装页面；否则不做处理，由上层继续抛出。
 *
 * 此函数独立于 Option.php，在 bootstrap 层统一拦截数据库异常，
 * 避免在 Option 等业务类中耦合安装跳转逻辑。
 *
 * @param \PDOException $e
 */
function check_database_error(\PDOException $e): void
{
    $message = $e->getMessage();

    // SQLite:  "no such table: xxx"
    // MySQL:   "Table 'xxx' doesn't exist" / "Base table or view not found: xxx"
    $tableMissing = preg_match('/no such table/i', $message)
        || preg_match("/Table\s+['`][^'`]+['`]\s+doesn't exist/i", $message)
        || preg_match('/Base table or view not found/i', $message);

    if ($tableMissing) {
        if (!headers_sent()) {
            header('Location: install/index.php');
        } else {
            echo '<script>location.href="install/index.php";</script>';
        }
        exit();
    }
}

// Install — 未安装时进入安装流程
if (!is_file('config/database.php') || !is_file('var/install.lock')) {
    header('Location: install/index.php');
    exit();
}

require "vendor/autoload.php";
$app = new \zap\App(dirname(__DIR__));

try {
    $app->run();
} catch (\PDOException $e) {
    check_database_error($e);
    // 非表缺失错误，继续抛出给 ErrorHandler 渲染错误页
    throw $e;
}
