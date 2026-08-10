<?php

namespace zapcms\controllers;

use zapcms\controllers\AdminController;
use zapcms\support\ZapUpdate;
use zap\http\Request;
use zap\http\Response;

/**
 * 系统更新控制器
 * 支持：检查更新、一键更新、手动上传更新、查看更新历史
 */
class UpdateController extends AdminController
{
    protected ZapUpdate $updater;

    public function __construct()
    {
        $this->updater = new ZapUpdate();
    }

    /**
     * 系统更新页面
     */
    public function index()
    {
        $updateInfo      = $this->updater->checkCoreUpdate();
        $pluginUpdates   = $this->updater->checkPluginUpdates();
        $updateHistory   = $this->updater->getUpdateHistory(10);
        $systemInfo      = $this->updater->getSystemInfo();
        $filePermissions = $this->updater->checkFilePermissions();
        $diskSpace       = $this->updater->checkDiskSpace();

        view('update.index', [
            'update_info'         => $updateInfo,
            'plugin_updates'      => $pluginUpdates,
            'update_history'      => $updateHistory,
            'system_info'         => $systemInfo,
            'file_permissions_ok' => $filePermissions,
            'disk_space_ok'       => $diskSpace,
        ]);
    }

    /**
     * AJAX: 检查核心更新
     */
    public function ajaxCheckCore()
    {
        $updateInfo = $this->updater->checkCoreUpdate();

        Response::json([
            'code' => $updateInfo ? 0 : 1,
            'data' => $updateInfo,
        ]);
    }

    /**
     * AJAX: 检查插件更新
     */
    public function ajaxCheckPlugins()
    {
        $updates = $this->updater->checkPluginUpdates();

        Response::json([
            'code'  => 0,
            'data'  => $updates,
            'count' => count($updates),
        ]);
    }

    /**
     * 执行系统更新
     */
    public function doUpdate()
    {
        if (!Request::isPost()) {
            Response::json(['code' => 1, 'msg' => '无效请求']);
        }

        $downloadUrl = trim(req()->post('download_url', ''));
        $newVersion  = trim(req()->post('version', ''));

        if (empty($downloadUrl) || empty($newVersion)) {
            Response::json(['code' => 1, 'msg' => '参数不完整']);
        }

        // 检查权限
        if (!$this->updater->checkFilePermissions()) {
            Response::json([
                'code' => 1,
                'msg'  => '文件系统权限不足，请确保以下目录可写：根目录、app、config、assets、storage',
            ]);
        }

        // 检查磁盘空间
        if (!$this->updater->checkDiskSpace()) {
            Response::json(['code' => 1, 'msg' => '磁盘空间不足，请至少保留50MB可用空间']);
        }

        $result = $this->updater->doCoreUpdate($downloadUrl, $newVersion);

        Response::json([
            'code' => $result['success'] ? 0 : 1,
            'msg'  => $result['message'],
        ]);
    }

    /**
     * 手动上传更新包
     */
    public function manualUpdate()
    {
        if (!Request::isPost()) {
            Response::json(['code' => 1, 'msg' => '无效请求']);
        }

        $file = $_FILES['update_zip'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Response::json(['code' => 1, 'msg' => '请上传有效的ZIP更新包']);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            Response::json(['code' => 1, 'msg' => '只支持ZIP格式的更新包']);
        }

        $newVersion = trim(req()->post('version', ''));

        $result = $this->updater->doManualUpdate($file['tmp_name'], $newVersion);

        Response::json([
            'code' => $result['success'] ? 0 : 1,
            'msg'  => $result['message'],
        ]);
    }

    /**
     * 查看系统环境信息
     */
    public function systemInfo()
    {
        $info = $this->updater->getSystemInfo();
        Response::json(['code' => 0, 'data' => $info]);
    }
}
