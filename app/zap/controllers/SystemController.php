<?php

namespace app\zap\controllers;

use app\zap\cms\backup\Database;
use zap\cms\AdminController;
use zap\cms\Mailer;
use zap\cms\Option;
use zap\http\Request;
use zap\http\Response;
use zap\view\View;

class SystemController extends AdminController
{
    function settings(){
        $keyPrefix = '^website\.';
        if(Request::isPost()){
            $options = Request::post('options',[]);
            $optionKeys = Option::getKeys($keyPrefix,'REGEXP');
            foreach ($options as $key=>$value){
                if(in_array($key,$optionKeys)){
                    Option::update($key,$value,null,1);
                }else{
                    Option::add($key,$value,0,1);
                }
            }
            Response::json(['code'=>0,'msg'=>'保存成功']);
        }
        $data = [
            'options'=> Option::getArray($keyPrefix,'REGEXP')
        ];
        View::render("system.settings",$data);
    }

    /**
     * 发送测试邮件
     */
    public function mailTest()
    {
        if (!Request::isPost()) {
            Response::json(['code' => 1, 'msg' => '非法请求']);
            return;
        }

        $testEmail = Request::post('test_email', '');

        if (empty($testEmail) || !filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
            Response::json(['code' => 1, 'msg' => '请输入有效的邮箱地址']);
            return;
        }

        try {
            $siteName = option('website.title', 'ZAP CMS');
            $subject  = "[{$siteName}] SMTP 测试邮件";
            $body     = "<h3>邮件发送测试成功！</h3><p>这是一封来自 <strong>{$siteName}</strong> 的 SMTP 配置测试邮件。</p><p>如果您能收到此邮件，说明 SMTP 邮件配置正确。</p><hr><p style='color:#999;font-size:12px;'>此邮件由系统自动发送，请勿回复。</p>";

            Mailer::send($testEmail, $subject, $body);

            Response::json(['code' => 0, 'msg' => "测试邮件已发送至 {$testEmail}，请检查收件箱"]);
        } catch (\Exception $e) {
            Response::json(['code' => 1, 'msg' => '邮件发送失败: ' . $e->getMessage()]);
        }
    }

    public function sysInfo()
    {
        View::render("system.sysinfo",[
            'page_title' => '服务器信息',
            'page_subtitle' => '系统运行环境与配置详情',
            'breadcrumbs' => [
                ['title' => '控制台', 'url' => \zap\facades\Url::action('Index')],
                ['title' => '服务器信息'],
            ],
        ]);
    }

    public function database(){
        \view('system.database',[
            'page_title' => '数据库管理',
            'page_subtitle' => '查看数据库信息、备份与还原',
            'breadcrumbs' => [
                ['title' => '控制台', 'url' => \zap\facades\Url::action('Index')],
                ['title' => '数据库管理'],
            ],
        ]);
    }

    public function backup(){
        if( Database::backup() === true){
            Response::json(['code'=>0,'msg'=>'备份成功']);
        }else{
            Response::json(['code'=>1,'msg'=>'备份失败']);
        }

    }

    public function backupList(){
        $backupDir = var_path('backups/sql');
        $files = [];

        if (is_dir($backupDir)) {
            $items = scandir($backupDir);
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $filePath = $backupDir . '/' . $item;
                if (is_file($filePath)) {
                    $files[] = [
                        'name' => $item,
                        'size' => filesize($filePath),
                        'mtime' => filemtime($filePath),
                        'path' => $filePath,
                    ];
                }
            }
            // 按修改时间倒序
            usort($files, function($a, $b) {
                return $b['mtime'] - $a['mtime'];
            });
        }

        View::render('system.backup-list', [
            'files' => $files,
            'page_title' => '备份列表',
            'page_subtitle' => '数据库备份文件管理',
            'breadcrumbs' => [
                ['title' => '控制台', 'url' => \zap\facades\Url::action('Index')],
                ['title' => '数据库管理', 'url' => \zap\facades\Url::action('System@database')],
                ['title' => '备份列表'],
            ],
        ]);
    }

    public function backupDownload($filename = null){
        if (empty($filename)) {
            Response::json(['code'=>1,'msg'=>'参数错误']);
            return;
        }
        $backupDir = var_path('backups/sql');
        $filePath = $backupDir . '/' . basename($filename);
        if (!is_file($filePath)) {
            http_response_code(404);
            echo '<h1>文件不存在</h1>';
            exit;
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function backupDelete(){
        if (!Request::isPost()) {
            Response::json(['code'=>1,'msg'=>'非法请求']);
            return;
        }
        $filename = Request::post('filename');
        if (empty($filename)) {
            Response::json(['code'=>1,'msg'=>'参数错误']);
            return;
        }
        $filePath = var_path('backups/sql') . '/' . basename($filename);
        if (!is_file($filePath)) {
            Response::json(['code'=>1,'msg'=>'文件不存在']);
            return;
        }
        if (unlink($filePath)) {
            Response::json(['code'=>0,'msg'=>'删除成功']);
        } else {
            Response::json(['code'=>1,'msg'=>'删除失败']);
        }
    }

    /**
     * Sitemap 管理页面
     */
    public function sitemap()
    {
        $siteUrl = rtrim(config('config.site_url', base_url()), '/');
        $types = ['catalog', 'page', 'article', 'product', 'faq'];

        $sitemaps = [];
        foreach ($types as $type) {
            $count = \zap\cms\models\Node::createQuery()
                ->where('node_type', $type)
                ->where('status', 'publish')
                ->count();
            if ($count > 0) {
                $sitemaps[] = [
                    'type'   => $type,
                    'url'    => $siteUrl . '/sitemap-' . $type . '.xml',
                    'count'  => $count,
                ];
            }
        }

        $data = [
            'page_title'    => 'Sitemap',
            'page_subtitle' => 'XML 站点地图管理与查看',
            'breadcrumbs'   => [
                ['title' => '控制台', 'url' => \zap\facades\Url::action('Index')],
                ['title' => '设置'],
                ['title' => 'Sitemap'],
            ],
            'sitemap_index_url' => $siteUrl . '/sitemap.xml',
            'sitemaps' => $sitemaps,
        ];

        \view('system.sitemap', $data);
    }

    /**
     * 固定链接设置页面
     */
    public function permalink()
    {
        if (Request::isPost()) {
            $structure     = Request::post('permalink_structure', '/%postname%/');
            $catalogPrefix = Request::post('catalog_prefix', 'catalog');

            // 基本清理
            $structure = '/' . trim(trim($structure), '/') . '/';
            $catalogPrefix = preg_replace('/[^a-zA-Z0-9_-]/', '', $catalogPrefix) ?: 'catalog';

            Option::update('permalink.structure', $structure, 0, 1);
            Option::update('permalink.catalog_prefix', $catalogPrefix, 0, 1);

            // 清除配置缓存
            \zap\facades\Cache::delete('permalink.structure');
            \zap\facades\Cache::delete('permalink.catalog_prefix');

            Response::json(['code' => 0, 'msg' => '固定链接设置已保存']);
        }

        $data = [
            'page_title'    => '固定链接设置',
            'page_subtitle' => '自定义网站链接结构，优化 SEO',
            'breadcrumbs'   => [
                ['title' => '控制台', 'url' => \zap\facades\Url::action('Index')],
                ['title' => '设置'],
                ['title' => '固定链接设置'],
            ],
            'current_structure'     => option('permalink.structure', '/%postname%/'),
            'current_catalog_prefix' => option('permalink.catalog_prefix', 'catalog'),
        ];

        \view('system.permalink', $data);
    }

}