<?php

namespace zap\view;

use \Exception;

class PHPRenderer extends ViewRenderer
{
    /**
     * 渲染视图（处理布局链）
     *
     * @param bool $output true=返回字符串, false=直接输出
     * @return string|null
     */
    public function render($output = false)
    {
        // 重置块（允许同一实例重复渲染）
        $this->view->blocks = [];

        // 渲染视图内容到 'content' 块
        $this->renderTemplate($this->view->viewFile, 'content');

        // 如果有布局，渲染布局到 'layout' 块
        $aliasName = 'content';
        if ($this->view->layout) {
            $aliasName = 'layout';
            $this->renderTemplate($this->view->layout, 'layout');
        }

        $result = $this->view->blocks[$aliasName] ?? '';

        if ($output) {
            return $result;
        }
        echo $result;
        return null;
    }

    /**
     * 渲染指定模板文件并存入块
     */
    public function renderTemplate($template, $aliasName = 'content'): void
    {
        $obLevel = ob_get_level();
        $errorLevel = error_reporting();

        // 生产环境：关闭错误显示（不抑制，由 PHP 错误处理器接管）
        if (!config('config.debug', false)) {
            error_reporting($errorLevel & ~E_WARNING);
        }

        ob_start();
        extract($this->view->params, EXTR_SKIP);
        extract(View::$globalData, EXTR_SKIP);

        try {
            if (!is_file($template)) {
                trigger_error("Template File: {$template} not found", E_USER_ERROR);
            }
            include $template;
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            error_reporting($errorLevel);
            throw $e;
        }

        error_reporting($errorLevel);
        $this->view->blocks[$aliasName] = ob_get_clean();
    }

    /**
     * 渲染局部模板（不存入块，不参与布局）
     */
    public function renderPartial($template, array $data = []): string
    {
        $obLevel = ob_get_level();
        $errorLevel = error_reporting();

        if (!config('config.debug', false)) {
            error_reporting($errorLevel & ~E_WARNING);
        }

        $params = array_merge($this->view->params, $data);
        ob_start();
        extract($params, EXTR_SKIP);
        extract(View::$globalData, EXTR_SKIP);

        try {
            if (!is_file($template)) {
                return '';
            }
            include $template;
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            error_reporting($errorLevel);
            throw $e;
        }

        error_reporting($errorLevel);
        return ob_get_clean();
    }
}
