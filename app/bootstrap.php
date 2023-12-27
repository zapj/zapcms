<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:09
 * @lastModified 2023/12/22 下午12:02
 *
 */

const ZAP_CMS_VERSION = '1.0.0';
const ZAP_CMS_RELEASE_DATE = '2023-12-27';

if (PHP_VERSION_ID < 70400) {
    exit('ZAP CMS requires PHP 7.4.0+.');
}

// Install

if (!is_file('config/config.php') ) {
    header('Location: install/index.php');
    exit();
}


require "vendor/autoload.php";

$app = new \zap\App(dirname(__DIR__));

$app->run();
