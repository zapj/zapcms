<?php

namespace zap;

use Throwable;
use zap\log\Log;
use zap\traits\SingletonTrait;
use zap\util\FileUtils;
use zap\view\ZView;

class ErrorHandler
{
    use SingletonTrait;

    /**
     * 不报告的异常类型列表（这些异常不写入日志）
     *
     * @var array<class-string>
     */
    protected static array $dontReport = [];

    /**
     * 可渲染回调（根据异常类型自定义渲染逻辑）
     *
     * @var array<class-string, callable>
     */
    protected static array $renderCallbacks = [];

    /**
     * 可报告回调（自定义日志记录逻辑）
     *
     * @var array<class-string, callable>
     */
    protected static array $reportCallbacks = [];

    /**
     * HTTP 状态码对应的自定义视图模板
     *
     * @var array<int, string>
     */
    protected static array $errorViews = [];

    /**
     * 是否使用 JSON 响应（API 模式）
     */
    protected static bool $forceJson = false;

    /**
     * 生产环境通用错误页面路径
     */
    protected static string $productionView = '';

    // ───────────────────── 注册与配置 ─────────────────────

    /**
     * 注册错误 / 异常 / 致命错误处理器
     */
    public static function register(): void
    {
        $handler = static::instance();

        set_error_handler([$handler, 'errorHandler']);
        set_exception_handler([$handler, 'exceptionHandler']);
        register_shutdown_function([$handler, 'shutdownHandler']);
    }

    /**
     * 设置不报告（不记日志）的异常类型
     *
     * @param array<class-string> $exceptions
     */
    public static function dontReport(array $exceptions): void
    {
        static::$dontReport = array_merge(static::$dontReport, $exceptions);
    }

    /**
     * 注册异常渲染回调
     *
     * @param class-string $exceptionClass 异常类名
     * @param callable $callback 回调函数，接收 Throwable，返回 string 或 Response
     */
    public static function renderable(string $exceptionClass, callable $callback): void
    {
        static::$renderCallbacks[$exceptionClass] = $callback;
    }

    /**
     * 注册异常报告回调
     *
     * @param class-string $exceptionClass
     * @param callable     $callback 回调函数，接收 Throwable，返回 true 表示阻止默认日志
     */
    public static function reportable(string $exceptionClass, callable $callback): void
    {
        static::$reportCallbacks[$exceptionClass] = $callback;
    }

    /**
     * 注册 HTTP 状态码对应的自定义视图
     *
     * @param array<int, string> $views  [404 => 'errors.404', 500 => 'errors.500']
     */
    public static function setErrorViews(array $views): void
    {
        static::$errorViews = array_merge(static::$errorViews, $views);
    }

    /**
     * 强制 JSON 响应模式（API 接口调试用）
     */
    public static function forceJson(bool $force = true): void
    {
        static::$forceJson = $force;
    }

    /**
     * 设置生产环境通用错误视图
     */
    public static function setProductionView(string $viewPath): void
    {
        static::$productionView = $viewPath;
    }

    // ───────────────────── 错误等级常量映射 ─────────────────────

    /**
     * 致命级别错误 — 必须终止脚本
     */
    protected static array $fatalErrors = [
        E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR,
        E_USER_ERROR, E_RECOVERABLE_ERROR, E_PARSE,
    ];

    /**
     * 将错误码转为人可读名称
     */
    protected function errorTypeString(int $errno): string
    {
        $map = [
            E_ERROR             => 'Fatal Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Strict Standards',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED        => 'Deprecated',
            E_USER_DEPRECATED   => 'User Deprecated',
        ];
        return $map[$errno] ?? "Unknown Error ({$errno})";
    }

    // ───────────────────── 致命错误处理器 ─────────────────────

    public function shutdownHandler(): void
    {
        $error = error_get_last();
        if ($error === null) {
            return;
        }

        // 仅在致命错误时处理，非致命错误由 errorHandler 处理
        if (!in_array($error['type'], static::$fatalErrors, true)) {
            return;
        }

        $this->clearOutputBuffer();

        if (app()->isConsole()) {
            $this->consoleError(
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
        } else {
            $html = $this->zapHighlightFile(
                $error['file'],
                $error['line'],
                $error['message'],
                'Fatal Error: ' . $this->errorTypeString($error['type'])
            );

            $this->renderErrorPage(500, $html, [
                'file'    => $error['file'],
                'line'    => $error['line'],
                'message' => $error['message'],
                'type'    => $this->errorTypeString($error['type']),
            ]);
        }
        exit(1);
    }

    // ───────────────────── 错误处理器 ─────────────────────

    public function errorHandler(int $errno, string $errstr, string $error_file, int $error_line): bool
    {
        // 尊重 PHP 的 error_reporting 配置（@ 抑制符也会走这里）
        if (!(error_reporting() & $errno)) {
            return true;
        }

        // 是否属于致命级别
        $isFatal = in_array($errno, static::$fatalErrors, true);

        $this->clearOutputBuffer();

        $errorType = $this->errorTypeString($errno);

        if (app()->isConsole()) {
            $this->consoleError($errno, $errstr, $error_file, $error_line);
        } elseif ($this->shouldReturnJson()) {
            // API / AJAX 请求：输出 JSON 错误，不渲染 HTML
            if (!headers_sent()) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
            }
            $payload = [
                'error'   => true,
                'code'    => 500,
                'message' => $this->isDebug() ? "{$errorType}: {$errstr}" : '服务器内部错误',
            ];
            if ($this->isDebug()) {
                $payload['file'] = $error_file;
                $payload['line'] = $error_line;
                $payload['type'] = $errorType;
            }
            echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit(1);
        } else {
            $html = $this->zapHighlightFile(
                $error_file,
                $error_line,
                $errstr,
                "PHP {$errorType} (Errno: {$errno})"
            );

            $this->renderErrorPage(500, $html, [
                'file'    => $error_file,
                'line'    => $error_line,
                'message' => $errstr,
                'type'    => $errorType,
                'errno'   => $errno,
            ]);
        }

        if ($isFatal) {
            $this->logError("Fatal Error (ErrNo {$errno}) - {$errorType}: {$errstr}", [
                'file' => $error_file,
                'line' => $error_line,
            ]);
            exit(1);
        }

        // 非致命错误：记录日志但不终止
        $this->logWarning("{$errorType}: {$errstr}", [
            'file' => $error_file,
            'line' => $error_line,
        ]);

        return true;
    }

    // ───────────────────── 异常处理器 ─────────────────────

    public function exceptionHandler(Throwable $exception): void
    {
        $this->clearOutputBuffer();

        // 报告（日志记录）
        $this->report($exception);

        // 渲染
        $this->render($exception);

        exit(1);
    }

    /**
     * 报告异常（记录日志）
     */
    public function report(Throwable $e): void
    {
        // 检查回调自定义报告
        foreach (static::$reportCallbacks as $class => $callback) {
            if ($e instanceof $class) {
                $result = $callback($e);
                if ($result === true) {
                    return; // 回调阻止默认日志
                }
            }
        }

        // 不报告的异常类型
        foreach (static::$dontReport as $class) {
            if ($e instanceof $class) {
                return;
            }
        }

        $context = [
            'message'   => $e->getMessage(),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'code'      => $e->getCode(),
            'trace'     => $this->formatTrace($e),
        ];

        // 附带 HTTP 请求上下文
        if (!$this->isConsole()) {
            $context['request'] = $this->requestContext();
        }

        $this->logError('Exception: ' . get_class($e), $context);
    }

    /**
     * 渲染异常页面
     */
    public function render(Throwable $e): void
    {
        // 检查回调自定义渲染
        foreach (static::$renderCallbacks as $class => $callback) {
            if ($e instanceof $class) {
                $result = $callback($e);
                if ($result !== null) {
                    echo $result;
                    return;
                }
            }
        }

        $statusCode = $this->getExceptionStatusCode($e);

        if ($this->isConsole()) {
            $this->consoleError(
                E_ERROR,
                get_class($e) . ': ' . $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
            return;
        }

        // JSON 模式（API 请求或强制 JSON）
        if ($this->shouldReturnJson()) {
            $this->renderJsonError($e, $statusCode);
            return;
        }

        // Debug 模式：显示详细错误
        if ($this->isDebug()) {
            $html = $this->renderExceptionHtml($e);
            $this->renderErrorPage($statusCode, $html, [
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'message'   => $e->getMessage(),
                'type'      => get_class($e),
                'exception' => $e,
            ]);
            return;
        }

        // 生产环境
        $this->renderProductionError($statusCode);
    }

    /**
     * 渲染异常详情 HTML
     */
    protected function renderExceptionHtml(Throwable $e): string
    {
        $html = $this->zapHighlightFile(
            $e->getFile(),
            $e->getLine(),
            $e->getMessage(),
            'Exception: ' . get_class($e)
        );

        // 渲染调用栈（过滤框架内部）
        $trace = $e->getTrace();
        foreach ($trace as $frame) {
            if (!isset($frame['file']) || !isset($frame['line'])) {
                continue;
            }
            // 过滤框架内部文件
            if ($this->isVendorFile($frame['file'])) {
                continue;
            }
            $html .= $this->zapHighlightFile(
                $frame['file'],
                $frame['line'],
                $this->formatTraceFrame($frame),
                get_class($e) . ' Stack'
            );
        }

        return $html;
    }

    // ───────────────────── 渲染输出 ─────────────────────

    /**
     * 渲染错误页面
     */
    protected function renderErrorPage(int $statusCode, string $html, array $context): void
    {
        http_response_code($statusCode);

        // 自定义视图
        if (isset(static::$errorViews[$statusCode])) {
            $viewName = static::$errorViews[$statusCode];
            try {
                ZView::render($viewName, $context);
                return;
            } catch (Throwable $viewError) {
                // 自定义视图渲染失败，回退到默认
            }
        }

        if ($this->isDebug()) {
            ZView::render(
                ZAP_SRC . '/resources/views/errors/error.php',
                ['html' => $html, 'handler' => $this, 'status' => $statusCode]
            );
        } else {
            $this->renderProductionError($statusCode);
        }
    }

    /**
     * 生产环境错误页面
     */
    protected function renderProductionError(int $statusCode): void
    {
        http_response_code($statusCode);

        if (static::$productionView) {
            try {
                ZView::render(static::$productionView, ['status' => $statusCode]);
                return;
            } catch (Throwable $e) {
                // 回退
            }
        }

        $viewMap = [
            404 => ZAP_SRC . '/resources/views/http/404.html',
            500 => ZAP_SRC . '/resources/views/http/500.html',
            503 => ZAP_SRC . '/resources/views/http/503.html',
        ];

        $view = $viewMap[$statusCode] ?? $viewMap[500];
        if (file_exists($view)) {
            readfile($view);
        } else {
            echo '<h1>' . $statusCode . '</h1><p>服务器内部错误</p>';
        }
    }

    /**
     * JSON 格式错误响应
     */
    protected function renderJsonError(Throwable $e, int $statusCode): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        $payload = [
            'error'   => true,
            'code'    => $statusCode,
            'message' => $statusCode >= 500 ? '服务器内部错误' : $e->getMessage(),
        ];

        if ($this->isDebug()) {
            $payload['exception'] = get_class($e);
            $payload['file']      = $e->getFile();
            $payload['line']      = $e->getLine();
            $payload['trace']     = $this->formatTrace($e);
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    // ───────────────────── 源码高亮 ─────────────────────

    /**
     * 生成带语法高亮的错误源代码片段 HTML
     *
     * @param string $filename 文件路径
     * @param int    $lineNo   出错行号
     * @param string $message  错误消息
     * @param string $title    标题
     * @param int    $offset   前后上下文行数
     * @return string HTML
     */
    public function zapHighlightFile(
        string $filename,
        int $lineNo,
        string $message = '',
        string $title = '错误信息',
        int $offset = 5
    ): string {
        if (!file_exists($filename) || !is_readable($filename)) {
            return '<div style="padding:10px;color:#a00;background:#fff0f0;border:1px solid #fcc;">'
                . '无法读取文件: ' . htmlspecialchars($filename) . '</div>';
        }

        // 使用手动高亮，避免 highlight_file 按 <br /> 拆分导致 HTML 标签破碎
        return $this->manualHighlightFile($filename, $lineNo, $message, $title, $offset);
    }

    /**
     * 手动语法高亮（highlight_file 不适用时的降级方案）
     */
    protected function manualHighlightFile(
        string $filename,
        int $lineNo,
        string $message,
        string $title,
        int $offset
    ): string {
        $source = file($filename);
        $totalLines = count($source);
        $startLine = max(1, $lineNo - $offset);
        $endLine   = min($totalLines, $lineNo + $offset);

        $sliced = [];
        for ($i = $startLine; $i <= $endLine; $i++) {
            $line = $source[$i - 1] ?? '';
            $sliced[] = $this->highlightLine($line);
        }

        $lineCount = count($sliced);
        return $this->buildHighlightHtml($filename, $message, $title, $startLine, $lineNo, $lineCount, $sliced);
    }

    /**
     * 简单的 PHP 语法高亮（不依赖 highlight_string 的 <?php ?> 包装）。
     */
    protected function highlightLine(string $line): string
    {
        $line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');

        $keywords = 'abstract|and|array|as|break|case|catch|class|clone|'
            . 'const|continue|declare|default|die|do|echo|else|elseif|'
            . 'empty|enddeclare|endfor|endforeach|endif|endswitch|endwhile|'
            . 'eval|exit|extends|final|finally|for|foreach|function|'
            . 'global|goto|if|implements|include|include_once|instanceof|'
            . 'insteadof|interface|isset|list|namespace|new|or|print|'
            . 'private|protected|public|require|require_once|return|'
            . 'switch|throw|trait|try|unset|use|var|while|xor|yield|'
            . 'true|false|null|self|parent|static|this|'
            . 'int|float|bool|string|void|iterable|object|callable|mixed|never';

        // Single-pass regex to avoid re-matching inside generated HTML tags
        $line = preg_replace_callback(
            '/'
            // 1: single-line comment
            . '(\/\/[^\n]*|\#[^\n]*)'
            // 2: multi-line comment
            . '|(\/\*.*?\*\/)'
            // 3: string literal
            . '|(&#(?:039|0(?:39|o47|x27));.*?&#(?:039|0(?:39|o47|x27));)'
            . '|(&quot;.*?&quot;)'
            // 4: variable
            . '|(\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)'
            // 5: keyword
            . '|(?<![a-zA-Z0-9_])(' . $keywords . ')(?![a-zA-Z0-9_])'
            // 6: number
            . '|(\b\d+(?:\.\d+)?\b)'
            . '/s',
            function ($m) {
                if (!empty($m[1])) return '<span style="color:#999;font-style:italic">' . $m[1] . '</span>';
                if (!empty($m[2])) return '<span style="color:#999;font-style:italic">' . $m[2] . '</span>';
                if (!empty($m[3]) || !empty($m[4])) return '<span style="color:#c00">' . $m[0] . '</span>';
                if (!empty($m[5])) return '<span style="color:#060">' . $m[5] . '</span>';
                if (!empty($m[6])) return '<span style="color:#00b">' . $m[6] . '</span>';
                if (!empty($m[7])) return '<span style="color:#f60">' . $m[7] . '</span>';
                return $m[0];
            },
            $line
        );

        return $line;
    }

    /**
     * 构建高亮 HTML
     */
    protected function buildHighlightHtml(
        string $filename,
        string $message,
        string $title,
        int $startLine,
        int $lineNo,
        int $lineCount,
        array $sliced
    ): string {
        $html = '';

        // 标题
        if ($title) {
            $html .= '<div style="
                padding: 8px 12px;
                color: #545454;
                background: #f0f4f8;
                border: 1px solid #d0d7de;
                border-bottom: none;
                font-size: 14px;
                font-weight: 600;
                border-radius: 6px 6px 0 0;
            ">' . htmlspecialchars($title) . '</div>';
        }

        // 错误消息
        if ($message) {
            $html .= '<div style="
                padding: 8px 12px;
                color: #a00;
                background: #fff5f5;
                border: 1px solid #fcc;
                border-top: none;
                font-size: 13px;
            ">' . htmlspecialchars($message) . '</div>';
        }

        // 文件名
        $displayPath = str_replace(['\\', $_SERVER['DOCUMENT_ROOT'] ?? '' . '/'], ['/', ''], $filename);
        $html .= '<div style="
            padding: 6px 12px;
            color: #666;
            background: #fafbfc;
            border: 1px solid #e1e4e8;
            border-top: none;
            font-size: 12px;
            font-family: monospace;
        ">文件: ' . htmlspecialchars($displayPath) . '</div>';

        // 代码行
        $html .= '<div style="
            border: 1px solid #d0d7de;
            border-top: none;
            border-radius: 0 0 6px 6px;
            overflow-x: auto;
            font-family: Consolas, "SF Mono", Monaco, "Courier New", monospace;
        ">';

        $padLen = strlen((string)($startLine + $lineCount));
        foreach ($sliced as $i => $line) {
            $currentLine = $startLine + $i;
            $isErrorLine = ($currentLine === $lineNo);

            $bg         = $isErrorLine ? '#fff0f0' : ($i % 2 ? '#f6f8fa' : '#fff');
            $numBg      = $isErrorLine ? '#fdd'    : '#f1f2f3';
            $numColor   = $isErrorLine ? '#c00'    : '#888';

            $lineNum = str_pad((string)$currentLine, $padLen, ' ', STR_PAD_LEFT);

            // 清理 highlight_file / highlight_string 残留的多余 <br> 标签与空行
            $display = ($line === '' || $line === "\n") ? '&nbsp;' : rtrim($line);
            $display = preg_replace('/<br\s*\/?>/i', '', $display);

            $html .= '<div style="display:flex;background:' . $bg . ';">';
            $html .= '<span style="
                display:inline-block;width:50px;padding:2px 8px;text-align:right;
                background:' . $numBg . ';color:' . $numColor . ';
                font-size:12px;line-height:1.5;user-select:none;flex-shrink:0;
            ">' . $lineNum . '</span>';
            $html .= '<span style="
                display:inline-block;padding:2px 10px;font-size:13px;line-height:1.5;
                white-space:pre;
            ">' . $display . '</span></div>';
        }

        $html .= '</div>';
        return $html;
    }

    // ───────────────────── 工具方法 ─────────────────────

    /**
     * 清除输出缓冲区
     */
    protected function clearOutputBuffer(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    /**
     * 是否 Debug 模式
     */
    protected function isDebug(): bool
    {
        return config('config.debug', false) === true;
    }

    /**
     * 是否控制台环境
     */
    protected function isConsole(): bool
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * 是否返回 JSON
     */
    protected function shouldReturnJson(): bool
    {
        if (static::$forceJson) {
            return true;
        }

        if (isset($_SERVER['HTTP_ACCEPT']) &&
            stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            return true;
        }

        // AJAX 请求
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }

        return false;
    }

    /**
     * 获取异常的 HTTP 状态码
     */
    protected function getExceptionStatusCode(Throwable $e): int
    {
        // 如果异常有 getStatusCode 方法（如 HttpException）
        if (method_exists($e, 'getStatusCode')) {
            return $e->getStatusCode();
        }

        // 按异常类型映射
        $classMap = [
            'zap\exception\NotFoundException'     => 404,
            'zap\exception\ViewNotFoundException'  => 500,
            'zap\exception\CurlException'          => 502,
            'zap\exception\NotSupportedException'  => 501,
        ];

        foreach ($classMap as $class => $code) {
            if ($e instanceof $class) {
                return $code;
            }
        }

        return 500;
    }

    /**
     * 请求上下文信息（用于日志）
     */
    protected function requestContext(): array
    {
        return [
            'url'    => ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://'
                        . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                        . ($_SERVER['REQUEST_URI'] ?? '/'),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'ua'     => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referer'=> $_SERVER['HTTP_REFERER'] ?? '',
        ];
    }

    /**
     * 格式化调用栈（用于日志）
     */
    protected function formatTrace(Throwable $e): array
    {
        $trace = [];
        foreach ($e->getTrace() as $i => $frame) {
            $trace[] = sprintf(
                '#%d %s%s%s(): %s',
                $i,
                $frame['file'] ?? '[internal]',
                isset($frame['line']) ? '(' . $frame['line'] . ')' : '',
                $frame['function'] ?? '',
                $frame['class'] ?? '' . ($frame['type'] ?? '') . ($frame['function'] ?? '')
            );
        }
        return array_slice($trace, 0, 50);
    }

    /**
     * 格式化单个调用帧
     */
    protected function formatTraceFrame(array $frame): string
    {
        $class    = $frame['class'] ?? '';
        $type     = $frame['type'] ?? '';
        $function = $frame['function'] ?? '';
        $args     = [];

        if (isset($frame['args']) && is_array($frame['args'])) {
            foreach ($frame['args'] as $arg) {
                if (is_string($arg)) {
                    $args[] = "'" . (strlen($arg) > 30 ? substr($arg, 0, 30) . '...' : $arg) . "'";
                } elseif (is_int($arg) || is_float($arg)) {
                    $args[] = (string)$arg;
                } elseif (is_bool($arg)) {
                    $args[] = $arg ? 'true' : 'false';
                } elseif (is_null($arg)) {
                    $args[] = 'null';
                } elseif (is_array($arg)) {
                    $args[] = 'Array(' . count($arg) . ')';
                } elseif (is_object($arg)) {
                    $args[] = get_class($arg);
                } else {
                    $args[] = gettype($arg);
                }
            }
        }

        $call = $class . $type . $function;
        return $call . '(' . implode(', ', $args) . ')';
    }

    /**
     * 判断是否为 vendor / 框架内部文件
     */
    protected function isVendorFile(string $file): bool
    {
        $file = str_replace('\\', '/', $file);
        return str_contains($file, '/vendor/') || str_contains($file, '/zap-php-framework/src/');
    }

    /**
     * 记录错误日志
     */
    protected function logError(string $message, array $context = []): void
    {
        if (class_exists('zap\log\Log')) {
            \zap\log\Log::emergency($message, $context);
        }
    }

    /**
     * 记录警告日志
     */
    protected function logWarning(string $message, array $context = []): void
    {
        if (class_exists('zap\log\Log')) {
            \zap\log\Log::warning($message, $context);
        }
    }

    /**
     * 命令行错误输出
     */
    protected function consoleError(int $errno, string $message, string $file, int $line): void
    {
        $type  = $this->errorTypeString($errno);
        $short = str_replace(['\\', dirname(dirname($file) ?: '/') . '/'], ['/', ''], $file);

        fwrite(STDERR, sprintf(
            "\033[31m[%s]\033[0m %s\n  at \033[33m%s\033[0m:\033[36m%d\033[0m\n",
            $type,
            $message,
            $short,
            $line
        ));
    }

    // ───────────────────── 快捷方法 ─────────────────────

    /**
     * 触发 HTTP 错误响应
     *
     * @param int    $statusCode HTTP 状态码
     * @param string $message    错误消息
     * @param array  $headers    额外响应头
     */
    public static function abort(int $statusCode = 500, string $message = '', array $headers = []): void
    {
        foreach ($headers as $key => $value) {
            header("{$key}: {$value}");
        }

        throw new exception\HttpException($statusCode, $message ?: static::defaultMessage($statusCode));
    }

    /**
     * 默认状态码消息
     */
    protected static function defaultMessage(int $statusCode): string
    {
        $messages = [
            400 => '错误的请求',
            401 => '未授权',
            403 => '禁止访问',
            404 => '页面未找到',
            405 => '方法不允许',
            408 => '请求超时',
            419 => '页面已过期',
            422 => '数据验证失败',
            429 => '请求过于频繁',
            500 => '服务器内部错误',
            502 => '网关错误',
            503 => '服务暂不可用',
            504 => '网关超时',
        ];
        return $messages[$statusCode] ?? '未知错误';
    }
}