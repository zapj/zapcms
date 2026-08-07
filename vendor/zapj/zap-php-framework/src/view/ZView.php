<?php

namespace zap\view;

use Throwable;

class ZView
{
    /**
     * 简化版视图渲染（无布局/块系统依赖，适合错误页面等底层场景）
     *
     * @param string $path   模板文件绝对路径
     * @param array  $data   模板数据
     * @param bool   $return true=返回字符串, false=直接输出
     * @return string|null
     * @throws Throwable
     */
    public static function render($path, $data = [], $return = false): ?string
    {
        if (!is_file($path)) {
            throw new \Exception("ZView: Template file not found: {$path}");
        }

        $obLevel = ob_get_level();
        $errorLevel = error_reporting();

        // 不再使用 error_reporting(0) 压制所有错误
        // 改为让 PHP 正常处理，由应用程序的错误处理器接管
        if (!config('config.debug', false)) {
            error_reporting($errorLevel & ~E_WARNING);
        }

        ob_start();
        extract($data, EXTR_SKIP);

        try {
            include $path;
        } catch (Throwable $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            error_reporting($errorLevel);
            throw $e;
        }

        error_reporting($errorLevel);
        $content = ob_get_clean();

        if ($return) {
            return $content;
        }
        echo $content;
        return null;
    }
}
