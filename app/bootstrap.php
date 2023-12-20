<?php

const ZAP_CMS_VERSION = '1.0.0';

if (PHP_VERSION_ID < 70400) {
    exit('ZAP CMS requires PHP 7.4.0+.');
}

// Install

if (!is_file('var/install.lock')) {
    header('Location: install/index.php');
    exit();
}


require "vendor/autoload.php";

$app = new \zap\App(dirname(__DIR__));

$app->run();
