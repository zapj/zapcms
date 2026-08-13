<?php
// 临时验证脚本：模拟 Web 子目录部署环境（localhost/zapcms）
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SCRIPT_NAME'] = '/zapcms/index.php';
$_SERVER['HTTPS'] = 'off';
$_SERVER['SERVER_PORT'] = 80;
$_SERVER['REQUEST_SCHEME'] = 'http';

chdir(__DIR__);
require __DIR__ . '/vendor/autoload.php';

if (!function_exists('base_url')) {
    require __DIR__ . '/vendor/zapj/zap-php-framework/src/functions.php';
}

new \zap\App(__DIR__);

echo 'base_url(): ', base_url(), "\n";
echo 'website.url option: ', var_export(option('website.url', ''), true), "\n";
echo 'upload.url_mode option: ', var_export(option('upload.url_mode', 'relative'), true), "\n";
echo 'get_site_base_url(): ', get_site_base_url(), "\n";
echo 'storage_url(a.jpg): ', storage_url('a.jpg'), "\n";
