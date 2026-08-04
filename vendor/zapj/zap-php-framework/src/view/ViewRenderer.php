<?php

namespace zap\view;

abstract class ViewRenderer
{
    /**
     * @var View
     */
    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    /**
     * 渲染视图
     *
     * @param bool $output true=返回字符串, false=直接输出
     * @return string|null
     */
    abstract public function render($output = false);

    /**
     * 渲染指定模板文件（内部使用，子类可覆盖）
     *
     * @param string $template  模板文件绝对路径
     * @param string $aliasName 块别名（用于布局系统）
     */
    public function renderTemplate($template, $aliasName = 'content'): void
    {
    }

    /**
     * 渲染局部模板，返回纯内容（不缓存为块，不参与布局）
     *
     * @param string $template 模板文件绝对路径
     * @param array  $data     额外数据
     * @return string
     */
    public function renderPartial($template, array $data = []): string
    {
        return '';
    }

    // ──────────── 委托给 View 的方法 ────────────

    public function layout($layout): void
    {
        $this->view->setLayout($layout);
    }

    public function extend($layout): void
    {
        $this->view->setLayout($layout);
    }

    public function include($name, $blockName = '_include'): void
    {
        $this->view->include($name, $blockName);
    }

    public function block($name): string
    {
        return $this->view->blocks[$name] ?? '';
    }

    public function section($name): string
    {
        return $this->block($name);
    }

    public function beginBlock($name): void
    {
        $this->view->beginBlock($name);
    }

    public function endBlock(): void
    {
        $this->view->endBlock();
    }

    /**
     * HTML 转义（在模板中使用：$this->e($var) 或 $this->esc($var)）
     */
    public function e($value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function esc($value): string
    {
        return $this->e($value);
    }
}
