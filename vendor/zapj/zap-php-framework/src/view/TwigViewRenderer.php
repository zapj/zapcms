<?php

namespace zap\view;

use \Exception;
use Twig\Loader\FilesystemLoader;

class TwigViewRenderer extends ViewRenderer
{
    /** @var FilesystemLoader */
    protected $loader;

    /** @var \Twig\Environment */
    protected $twigEngine;

    public function __construct($view)
    {
        parent::__construct($view);

        $this->loader = new FilesystemLoader(View::getPath());

        $templatePaths = config('twig.template_paths', []);
        foreach ($templatePaths as $namespace => $templateDir) {
            $ns = is_int($namespace) ? FilesystemLoader::MAIN_NAMESPACE : $namespace;
            $this->loader->addPath($templateDir, $ns);
        }

        $options = config('twig.options', ['cache' => false, 'debug' => true]);
        $options['debug'] = config('config.debug', false);

        $this->twigEngine = new \Twig\Environment($this->loader, $options);

        $extensions = config('twig.extensions', []);
        foreach ($extensions as $extension) {
            if (is_string($extension) && class_exists($extension)) {
                $this->twigEngine->addExtension(new $extension());
            } elseif (is_array($extension) && isset($extension[0]) && class_exists($extension[0])) {
                $this->twigEngine->addExtension(
                    new $extension[0](...($extension[1] ?? []))
                );
            }
        }
    }

    /**
     * 渲染 Twig 视图
     */
    public function render($output = false)
    {
        // 合并全局数据（不覆盖已有同名本地数据）
        foreach (View::$globalData as $name => $value) {
            if (!array_key_exists($name, $this->view->params)) {
                $this->view->params[$name] = $value;
            }
        }

        // 支持布局：把布局名传入模板变量
        if ($this->view->layout) {
            $this->view->params['_zap_layout'] = $this->view->layout;
        }

        if ($output) {
            return $this->twigEngine->render(
                $this->getTemplateName($this->view->viewFile),
                $this->view->params
            );
        }
        $this->twigEngine->display(
            $this->getTemplateName($this->view->viewFile),
            $this->view->params
        );
        return null;
    }

    /**
     * 渲染局部模板
     */
    public function renderPartial($template, array $data = []): string
    {
        $params = array_merge($this->view->params, $data);
        foreach (View::$globalData as $name => $value) {
            if (!array_key_exists($name, $params)) {
                $params[$name] = $value;
            }
        }
        return $this->twigEngine->render(
            $this->getTemplateName($template),
            $params
        );
    }

    /**
     * 获取 Twig 模板名称（相对于 loader 根路径）
     *
     * 修复：不再仅使用 basename()，保留子目录路径
     */
    private function getTemplateName($fullPath): string
    {
        $fullPath = str_replace('\\', '/', $fullPath);
        foreach (View::getPath() as $basePath) {
            $basePath = rtrim(str_replace('\\', '/', $basePath), '/');
            if (strpos($fullPath, $basePath . '/') === 0) {
                $relative = substr($fullPath, strlen($basePath) + 1);
                // 去掉扩展名（Twig 自动处理）
                $relative = preg_replace('/\.twig$/i', '', $relative);
                return $relative;
            }
        }
        // 兜底：使用 basename 去掉扩展名
        return preg_replace('/\.twig$/i', '', basename($fullPath));
    }

    public function getLoader(): FilesystemLoader
    {
        return $this->loader;
    }

    public function getEnvironment(): \Twig\Environment
    {
        return $this->twigEngine;
    }
}
