<?php

namespace app\zap\controllers;

use app\zap\cms\backup\Database;
use zap\cms\AdminController;
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


}