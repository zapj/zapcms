<?php

namespace zapcms\support;

/**
 * ZAP CMS 更新管理器
 * 负责检查系统核心和插件的版本更新
 */
class ZapUpdate
{
    protected ZapPackageManager $pm;

    public function __construct()
    {
        $this->pm = new ZapPackageManager();
    }

    // ==================== 核心更新 ====================

    /**
     * 检查系统核心是否有新版本
     *
     * @return array|null 返回null表示检查失败
     */
    public function checkCoreUpdate(): ?array
    {
        $response = $this->pm->apiGet('update/check', [
            'version'   => ZAP_CMS_VERSION,
            'php'       => PHP_VERSION,
            'mysql'     => $this->getDbVersion(),
            'site_url'  => ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? ''),
        ]);

        if (!$response) {
            return null;
        }

        return [
            'has_update'      => $response['has_update'] ?? false,
            'current_version' => ZAP_CMS_VERSION,
            'latest_version'  => $response['latest_version'] ?? ZAP_CMS_VERSION,
            'download_url'    => $response['download_url'] ?? '',
            'changelog'       => $response['changelog'] ?? '',
            'release_date'    => $response['release_date'] ?? '',
            'package_size'    => $response['package_size'] ?? 0,
            'is_critical'     => $response['is_critical'] ?? false,
        ];
    }

    /**
     * 执行系统核心更新
     *
     * @param string $downloadUrl 下载地址
     * @param string $newVersion  新版本号
     * @return array
     */
    public function doCoreUpdate(string $downloadUrl, string $newVersion): array
    {
        // 检查URL是否有效
        if (empty($downloadUrl)) {
            return ['success' => false, 'message' => '更新地址无效'];
        }

        // 检查文件系统可写
        if (!$this->checkFilePermissions()) {
            return ['success' => false, 'message' => '文件系统权限不足，请确保项目目录可写'];
        }

        // 检查磁盘空间
        if (!$this->checkDiskSpace()) {
            return ['success' => false, 'message' => '磁盘空间不足（需要至少50MB可用空间）'];
        }

        // 下载更新包
        $zipFile = $this->pm->download($downloadUrl, 'zapcms_update_' . $newVersion . '.zip');
        if (!$zipFile) {
            return ['success' => false, 'message' => '更新包下载失败，请检查网络连接'];
        }

        // 应用更新
        return $this->pm->applySystemUpdate($zipFile, $newVersion);
    }

    /**
     * 手动上传更新包进行更新
     *
     * @param string $zipFilePath 上传的ZIP文件路径
     * @param string $newVersion  新版本号
     * @return array
     */
    public function doManualUpdate(string $zipFilePath, string $newVersion): array
    {
        if (!file_exists($zipFilePath)) {
            return ['success' => false, 'message' => '更新包文件不存在'];
        }

        if (!$this->checkFilePermissions()) {
            return ['success' => false, 'message' => '文件系统权限不足'];
        }

        return $this->pm->applySystemUpdate($zipFilePath, $newVersion);
    }

    // ==================== 插件更新检查 ====================

    /**
     * 检查所有已安装插件的更新
     *
     * @return array 需要更新的插件列表
     */
    public function checkPluginUpdates(): array
    {
        $plugins  = $this->pm->getInstalledPlugins();
        $updates  = [];

        if (empty($plugins)) {
            return $updates;
        }

        $packages = array_column($plugins, 'package_name');
        $versions = [];
        foreach ($plugins as $p) {
            $versions[$p['package_name']] = $p['version'];
        }

        $response = $this->pm->apiPost('plugin/check-updates', [
            'packages' => $packages,
            'versions' => $versions,
        ]);

        if (!$response || empty($response['updates'])) {
            return $updates;
        }

        foreach ($response['updates'] as $update) {
            foreach ($plugins as $plugin) {
                if ($plugin['package_name'] === $update['package_name']) {
                    $updates[] = [
                        'name'          => $plugin['name'],
                        'title'         => $plugin['title'],
                        'package_name'  => $plugin['package_name'],
                        'current_version' => $plugin['version'],
                        'latest_version'  => $update['latest_version'],
                        'download_url'    => $update['download_url'] ?? '',
                        'changelog'       => $update['changelog'] ?? '',
                    ];
                    break;
                }
            }
        }

        return $updates;
    }

    /**
     * 执行单个插件更新
     *
     * @param string $name        插件名
     * @param string $downloadUrl 下载地址
     * @param string $version     新版本
     * @return array
     */
    public function doPluginUpdate(string $name, string $downloadUrl, string $version): array
    {
        return $this->pm->updatePlugin($name, $downloadUrl, $version);
    }

    // ==================== 插件市场 ====================

    /**
     * 从远程获取插件市场列表
     *
     * @param int    $page     页码
     * @param int    $perPage  每页条数
     * @param string $search   搜索关键词
     * @param string $category 分类
     * @return array|null
     */
    public function getMarketPlugins(int $page = 1, int $perPage = 12, string $search = '', string $category = ''): ?array
    {
        return $this->pm->apiGet('plugin/list', [
            'page'     => $page,
            'per_page' => $perPage,
            'search'   => $search,
            'category' => $category,
            'version'  => ZAP_CMS_VERSION,
        ]);
    }

    /**
     * 获取单个市场插件详情
     */
    public function getMarketPluginDetail(string $packageName): ?array
    {
        return $this->pm->apiGet('plugin/detail', [
            'package' => $packageName,
        ]);
    }

    /**
     * 安装市场插件
     */
    public function installMarketPlugin(string $packageName): array
    {
        // 获取远程插件详情
        $detail = $this->getMarketPluginDetail($packageName);

        if (!$detail) {
            return ['success' => false, 'message' => '获取插件信息失败'];
        }

        if (empty($detail['download_url'])) {
            return ['success' => false, 'message' => '插件下载地址无效'];
        }

        return $this->pm->installPlugin(
            $detail['package_name'] ?? $packageName,
            $detail['download_url'],
            $detail['version'] ?? '1.0.0'
        );
    }

    // ==================== 工具方法 ====================

    /**
     * 检查关键文件和目录是否可写
     */
    public function checkFilePermissions(): bool
    {
        $checkPaths = [
            APP_ROOT,
            APP_ROOT . '/app',
            APP_ROOT . '/config',
            APP_ROOT . '/assets',
            APP_ROOT . '/storage',
            APP_ROOT . '/mods',
        ];

        foreach ($checkPaths as $path) {
            if (file_exists($path) && !is_writable($path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 检查磁盘空间
     *
     * @param int $requiredMB 需要的磁盘空间(MB)
     * @return bool
     */
    public function checkDiskSpace(int $requiredMB = 50): bool
    {
        $freeSpace = disk_free_space(APP_ROOT);
        if ($freeSpace === false) {
            return true; // 无法检查则放行
        }
        return $freeSpace > ($requiredMB * 1024 * 1024);
    }

    /**
     * 获取当前数据库版本
     */
    protected function getDbVersion(): string
    {
        try {
            $result = DB::select('SELECT VERSION() as version');
            return $result[0]['version'] ?? 'unknown';
        } catch (\Throwable $e) {
            return 'unknown';
        }
    }

    /**
     * 获取系统环境信息（用于调试和提交反馈时）
     */
    public function getSystemInfo(): array
    {
        return [
            'zapcms_version' => ZAP_CMS_VERSION,
            'php_version'    => PHP_VERSION,
            'php_os'         => PHP_OS,
            'php_sapi'       => PHP_SAPI,
            'mysql_version'  => $this->getDbVersion(),
            'server'         => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'disk_free'      => $this->formatBytes(disk_free_space(APP_ROOT)),
            'memory_limit'   => ini_get('memory_limit'),
            'max_execution'  => ini_get('max_execution_time'),
            'upload_max'     => ini_get('upload_max_filesize'),
            'extensions'     => $this->getExtensions(),
        ];
    }

    /**
     * 获取已加载的关键PHP扩展
     */
    protected function getExtensions(): array
    {
        $keyExtensions = ['curl', 'zip', 'pdo', 'pdo_mysql', 'json', 'mbstring', 'gd', 'xml', 'openssl'];
        $loaded = [];
        foreach ($keyExtensions as $ext) {
            $loaded[$ext] = extension_loaded($ext);
        }
        return $loaded;
    }

    /**
     * 格式化字节数
     */
    protected function formatBytes(float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }

    /**
     * 获取更新历史记录
     */
    public function getUpdateHistory(int $limit = 20): array
    {
        return $this->pm->getUpdateHistory($limit);
    }
}
