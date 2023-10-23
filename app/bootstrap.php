<?php

const ZAP_CMS_VERSION = '1.0.0';

if (version_compare(phpversion(), '7.4.0', '<')) {
    exit('PHP7.4+ Required');
}

// Install

if (!is_file('var/install.lock')) {
    header('Location: install/index.php');
    exit();
}


require "vendor/autoload.php";

$app = new \zap\App(dirname(__DIR__));

$app->run();
