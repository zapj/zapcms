<?php

namespace zap\console;

class Input
{
    private array $argv;
    protected array $params = [];
    protected array $arguments = [];
    protected array $options = [];

    /** @var array 在 -- 之后的所有参数 */
    protected array $extra = [];

    public function __construct(?array $argv = null)
    {
        if ($argv === null) {
            $argv = $_SERVER['argv'] ?? [];
        }
        // 去掉 script name
        array_shift($argv);
        $this->argv = array_values($argv);
        $this->parseArgs();
    }

    /**
     * 解析命令行参数
     * 支持：
     *   - 位置参数:  arg1 arg2 "arg 3"
     *   - 长选项:    --name=value 或 --name value
     *   - 短选项:    -a -b -c value
     *   - 标志选项:  -v --verbose（值为 true）
     *   - 结束符:    -- 之后的所有参数视为位置参数
     */
    protected function parseArgs(): void
    {
        $argv = $this->argv;
        $positional = 0;
        $endOfOptions = false;

        while (count($argv) > 0) {
            $current = array_shift($argv);

            if ($current === '--') {
                $endOfOptions = true;
                continue;
            }

            if ($endOfOptions || !str_starts_with($current, '-')) {
                $this->params[] = $current;
                $this->arguments[$positional++] = $current;
                continue;
            }

            if ($current === '-') {
                // 单独的 '-' 被视为位置参数
                $this->params[] = $current;
                $this->arguments[$positional++] = $current;
                continue;
            }

            // 长选项 --key=value
            if (str_starts_with($current, '--')) {
                $current = substr($current, 2);

                if (str_contains($current, '=')) {
                    [$key, $value] = explode('=', $current, 2);
                } else {
                    $key = $current;
                    $next = $argv[0] ?? null;
                    // 下一个参数如果不是选项则作为值
                    if ($next !== null && !str_starts_with($next, '-')) {
                        $value = array_shift($argv);
                    } else {
                        $value = true;
                    }
                }

                $this->params[$key] = $value;
                $this->options[$key] = $value;
                continue;
            }

            // 短选项 -abc 或 -a value
            $current = substr($current, 1); // 去掉 '-'
            $len = strlen($current);

            for ($i = 0; $i < $len; $i++) {
                $key = $current[$i];

                // 最后一个字符且后面有非选项参数时作为选项值
                if ($i === $len - 1) {
                    $next = $argv[0] ?? null;
                    if ($next !== null && !str_starts_with($next, '-')) {
                        $this->params[$key] = array_shift($argv);
                        $this->options[$key] = $this->params[$key];
                    } else {
                        $this->params[$key] = true;
                        $this->options[$key] = true;
                    }
                } else {
                    $this->params[$key] = true;
                    $this->options[$key] = true;
                }
            }
        }
    }

    /**
     * 获取位置参数（1-based 索引兼容旧接口）
     */
    public function getParam($name, $default = null)
    {
        if (is_int($name)) {
            return $this->params[$name - 1] ?? $default;
        }
        return $this->params[$name] ?? $default;
    }

    /**
     * 按索引获取参数（0-based）
     */
    public function getArgument(int $index, $default = null)
    {
        return $this->arguments[$index] ?? $default;
    }

    /**
     * 获取所有位置参数
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * 获取命名选项
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * 获取所有选项
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function hasParam($name): bool
    {
        if (is_int($name)) {
            return isset($this->params[$name - 1]);
        }
        return array_key_exists($name, $this->params);
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }

    public function setArgv(array $argv): void
    {
        $this->argv = $argv;
    }

    public function getArgv(): array
    {
        return $this->argv;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
