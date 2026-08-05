<?php

namespace zap\view;

use zap\exception\ViewNotFoundException;
use zap\util\Str;

class View
{
    /** @var string|false 布局模板路径，false 表示无布局 */
    public $layout = false;

    /** @var string 视图名称（点分路径如 'users.profile'） */
    public $viewName;

    /** @var string 视图文件的完整磁盘路径 */
    public $viewFile;

    /** @var array 模板局部变量 */
    public $params = [];

    /** @var array 已渲染的块内容 ['blockName' => 'html'] */
    public $blocks = [];

    /** @var array 块栈（用于嵌套 beginBlock/endBlock） */
    private $_blocksStack = [];

    /** @var array 模板搜索路径列表 */
    protected static $templatePaths = [];

    /** @var array 所有视图实例共享的全局数据 */
    public static $globalData = [];

    /** @var string[] 支持的模板扩展名 */
    protected static $extensions = ['php', 'twig', 'html'];

    /** @var bool 是否已注册 include_path（避免重复修改全局状态） */
    protected static $includePathRegistered = false;

    /** @var bool 是否启用自动注册 include_path（默认不修改全局状态） */
    protected static $autoIncludePath = false;

    /**
     * @var ViewRenderer
     */
    private $engine;

    /**
     * 构造函数
     *
     * @param string|null $name 视图名（点分路径）
     * @param array       $data 模板数据
     * @throws ViewNotFoundException
     */
    public function __construct($name = null, $data = [])
    {
        $this->params = $data;
        $this->viewName = $name;

        $this->registerTemplatePaths();
        $this->prepare($name);
    }

    // ───────────────────── 静态工厂 & 工具 ─────────────────────

    /**
     * 静态工厂：从名称和数据创建 View 实例
     */
    public static function make($name = null, $data = []): View
    {
        return new View($name, $data);
    }

    /**
     * 渲染模板（静态便捷方法）
     *
     * @param string $template 模板名称
     * @param array  $data     模板数据
     * @param bool   $output   是否返回字符串（true）还是直接输出（false）
     * @return string|null
     */
    public static function render(string $template, array $data = [], bool $output = false): ?string
    {
        $view = View::make($template, $data);
        return $view->display($output);
    }

    /**
     * 检查模板是否存在
     *
     * @param string $name 模板名称
     * @return bool
     */
    public static function exists($name): bool
    {
        return static::resolveTemplate(static::normalizeName($name)) !== null;
    }

    /**
     * 返回第一个存在的模板路径（用于模板备选逻辑）
     *
     * @param string ...$names
     * @return string
     * @throws ViewNotFoundException
     */
    public static function first(...$names): string
    {
        foreach ($names as $name) {
            if (static::exists($name)) {
                return $name;
            }
        }
        throw new ViewNotFoundException(
            'None of the templates found: ' . implode(', ', $names)
        );
    }

    // ───────────────────── 模板路径管理 ─────────────────────

    /**
     * 获取或设置模板搜索路径
     *
     * @param string|null $path 添加路径到列表头部；null 则只读
     * @return array
     */
    public static function paths($path = null): array
    {
        if ($path !== null) {
            static::addPath($path);
        }
        return static::$templatePaths;
    }

    /**
     * 获取模板搜索路径（只读）
     */
    public static function getPath(): array
    {
        return static::$templatePaths;
    }

    /**
     * 添加模板搜索路径
     *
     * @param string $path   模板目录路径
     * @param bool   $append true 追加到末尾 / false 插入到头部（优先级更高）
     */
    public static function addPath($path = null, $append = false): void
    {
        if ($path === null || $path === '') {
            return;
        }
        $path = rtrim($path, '/\\');
        if (!$append) {
            array_unshift(static::$templatePaths, $path);
        } else {
            static::$templatePaths[] = $path;
        }
    }

    /**
     * 清空模板搜索路径
     */
    public static function clearPaths(): void
    {
        static::$templatePaths = [];
    }

    /**
     * 设置/获取是否自动修改 PHP include_path
     *
     * @param bool|null $enabled null=只读
     * @return bool
     */
    public static function autoIncludePath($enabled = null): bool
    {
        if ($enabled !== null) {
            static::$autoIncludePath = (bool)$enabled;
        }
        return static::$autoIncludePath;
    }

    /**
     * 注册自定义扩展名
     *
     * @param string $ext 扩展名（不含点，如 'phtml'）
     */
    public static function registerExtension(string $ext): void
    {
        $ext = ltrim($ext, '.');
        if (!in_array($ext, static::$extensions, true)) {
            static::$extensions[] = $ext;
        }
    }

    // ───────────────────── 全局数据共享 ─────────────────────

    /**
     * 向所有视图共享数据
     */
    public static function share($name, $value): void
    {
        static::$globalData[$name] = $value;
    }

    /**
     * 向所有视图共享数据（同 share）
     */
    public static function set($name, $value): void
    {
        static::$globalData[$name] = $value;
    }

    // ───────────────────── 链式设值 ─────────────────────

    /**
     * 链式设置单个模板变量
     *
     * @return $this
     */
    public function with($key, $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * 链式批量设置模板变量
     *
     * @return $this
     */
    public function withData(array $data): self
    {
        $this->params = array_merge($this->params, $data);
        return $this;
    }

    /**
     * 设置布局
     *
     * @param string $layout 布局模板名称（点分路径）
     * @return $this
     */
    public function withLayout($layout): self
    {
        $this->setLayout($layout);
        return $this;
    }

    // ───────────────────── 魔术方法 ─────────────────────

    public function __get($name)
    {
        return $this->params[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->params[$name]);
    }

    public function __unset($name)
    {
        unset($this->params[$name]);
    }

    public function __toString()
    {
        try {
            return $this->display(true) ?? '';
        } catch (\Throwable $e) {
            return '<!-- View Error: ' . htmlspecialchars($e->getMessage()) . ' -->';
        }
    }

    // ───────────────────── 布局 & 模板操作 ─────────────────────

    /**
     * 设置或读取布局模板
     *
     * @param string|null $layout 布局名；null 则返回当前布局
     * @return $this|string|false
     */
    public function setLayout($layout): void
    {
        $this->layout = $this->resolveTemplate($layout);
    }

    public function layout($layout): void
    {
        $this->setLayout($layout);
    }

    public function extend($layout): void
    {
        $this->setLayout($layout);
    }

    /**
     * 包含子模板（在模板内使用）
     */
    public function include($name, $blockName = '_include'): void
    {
        $template = $this->resolveTemplate($name);
        if ($template === null) {
            return;
        }
        $this->engine->renderTemplate($template, $blockName);
    }

    /**
     * 包含子模板并返回内容（不缓存为块）
     *
     * @param string $name
     * @param array  $data 额外数据（合并但不会覆盖主视图数据）
     * @return string
     */
    public function partial($name, array $data = []): string
    {
        $template = $this->resolveTemplate($name);
        if ($template === null) {
            return '';
        }
        return $this->engine->renderPartial($template, $data);
    }

    // ───────────────────── 块操作 ─────────────────────

    /**
     * 获取块内容
     */
    public function block($name): string
    {
        return $this->blocks[$name] ?? '';
    }

    /**
     * 输出块内容（同 block，向后兼容 section 别名）
     */
    public function section($name): string
    {
        return $this->block($name);
    }

    /**
     * 开始定义一个块
     */
    public function beginBlock($name): void
    {
        ob_start();
        $this->_blocksStack[] = $name;
    }

    /**
     * 结束当前块定义
     */
    public function endBlock(): void
    {
        $blockName = array_pop($this->_blocksStack);
        $this->blocks[$blockName] = rtrim(ob_get_clean());
    }

    // ───────────────────── 渲染 ─────────────────────

    /**
     * 渲染并返回/输出
     *
     * @param bool $output true=返回字符串, false=直接输出
     * @return string|null
     */
    public function display($output = false): ?string
    {
        return $this->engine->render($output);
    }

    /**
     * 直接输出视图
     */
    public function show(): void
    {
        $this->display(false);
    }

    /**
     * 返回渲染结果字符串
     *
     * @return string
     */
    public function fetch(): string
    {
        return $this->display(true) ?? '';
    }

    /**
     * 渲染内联模板字符串
     *
     * @param string $templateString PHP 模板内容
     * @param array  $data           模板数据
     * @return string
     */
    public static function renderString(string $templateString, array $data = []): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'zap_vw_') . '.php';
        file_put_contents($tmpFile, $templateString);
        try {
            $view = new self();
            $view->params = $data;
            $view->viewFile = $tmpFile;
            $view->viewName = '__string__';
            $view->initViewRenderer();
            $result = $view->engine->render(true);
            @unlink($tmpFile);
            return $result ?? '';
        } catch (\Throwable $e) {
            @unlink($tmpFile);
            throw $e;
        }
    }

    // ───────────────────── 内部方法 ─────────────────────

    /**
     * 获取视图文件路径
     */
    public function getViewFile(): string
    {
        return $this->viewFile;
    }

    /**
     * 获取渲染引擎
     *
     * @return ViewRenderer
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * 解析模板路径（静态方法，可在外部调用通过 exists()）
     *
     * @param string $template 模板名称
     * @return string|null
     */
    public static function resolveTemplate($template): ?string
    {
        $template = static::normalizeName($template);
        foreach (static::$templatePaths as $tplPath) {
            foreach (static::$extensions as $ext) {
                $tplFullPath = "{$tplPath}/{$template}.{$ext}";
                if (is_file($tplFullPath)) {
                    return $tplFullPath;
                }
            }
        }
        return null;
    }

    /**
     * 注册模板搜索路径（主题优先，默认路径兜底）
     */
    private function registerTemplatePaths(): void
    {
        if (($theme = config('config.theme', false)) !== false) {
            $themePath = themes_path("$theme");
            if (!in_array($themePath, static::$templatePaths, true)) {
                array_unshift(static::$templatePaths, $themePath);
            }
        } else {
            $defaultPath = base_path('app/views');
            if (!in_array($defaultPath, static::$templatePaths, true)) {
                array_unshift(static::$templatePaths, $defaultPath);
            }
        }

        // 只在使用者显式开启时才修改全局 include_path（默认不修改）
        if (static::$autoIncludePath && !static::$includePathRegistered) {
            set_include_path(
                get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, static::$templatePaths)
            );
            static::$includePathRegistered = true;
        }
    }

    /**
     * 预处理：解析模板并创建渲染引擎
     */
    private function prepare($name): void
    {
        $this->viewFile = static::resolveTemplate($name);
        if ($this->viewFile === null) {
            throw new ViewNotFoundException(
                'Template file not found: ' . $name
                . '. Searched paths: [' . implode(', ', static::$templatePaths) . ']'
            );
        }
        $this->initViewRenderer();
    }

    /**
     * 初始化渲染引擎
     */
    private function initViewRenderer(): void
    {
        $ext = pathinfo($this->viewFile, PATHINFO_EXTENSION);
        if ($ext === 'twig') {
            $this->engine = new TwigViewRenderer($this);
        } else {
            $this->engine = new PHPRenderer($this);
        }
    }

    /**
     * 标准化模板名称：将点分路径转为目录路径
     */
    private static function normalizeName($name): string
    {
        return str_replace('.', '/', (string)$name);
    }
}
