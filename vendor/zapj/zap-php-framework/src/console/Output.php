<?php

namespace zap\console;

class Output
{
    /** @var resource */
    private $stdout;

    /** @var resource */
    private $stderr;

    private Input $input;
    protected int $verbose = 0;
    protected bool $colorSupport;

    // ANSI 颜色码
    private const COLORS = [
        'default'   => '39',
        'black'     => '30',
        'red'       => '31',
        'green'     => '32',
        'yellow'    => '33',
        'blue'      => '34',
        'magenta'   => '35',
        'cyan'      => '36',
        'white'     => '37',
        'gray'      => '90',
    ];

    private const STYLES = [
        'info'      => '32',       // green
        'comment'   => '33',       // yellow
        'error'     => '31',       // red
        'warning'   => '33',       // yellow
        'debug'     => '90',       // gray
        'success'   => '32',       // green
    ];

    public function __construct(Input $input)
    {
        $this->stdout = $this->openOutputStream();
        $this->stderr = $this->openErrorStream();
        $this->input = $input;
        $this->colorSupport = $this->detectColorSupport();

        // 修复 verbose 判断: -v / -vv / -vvv 需要从长到短匹配
        if ($this->input->hasParam('vvv')) {
            $this->verbose = 3;
        } elseif ($this->input->hasParam('vv')) {
            $this->verbose = 2;
        } elseif ($this->input->hasParam('v')) {
            $this->verbose = 1;
        }
    }

    public function getVerbose(): int
    {
        return $this->verbose;
    }

    /**
     * @return resource|false
     */
    public function getStderr()
    {
        return $this->stderr;
    }

    public function getStdout()
    {
        return $this->stdout;
    }

    private function openOutputStream()
    {
        return \defined('STDOUT') ? \STDOUT : (@fopen('php://stdout', 'w') ?: fopen('php://output', 'w'));
    }

    private function openErrorStream()
    {
        return \defined('STDERR') ? \STDERR : (@fopen('php://stderr', 'w') ?: fopen('php://output', 'w'));
    }

    // ========== 颜色支持 ==========

    protected function detectColorSupport(): bool
    {
        // Follow https://no-color.org/
        if (isset($_SERVER['NO_COLOR']) || false !== getenv('NO_COLOR')) {
            return false;
        }

        if ('Hyper' === getenv('TERM_PROGRAM')) {
            return true;
        }

        if (\DIRECTORY_SEPARATOR === '\\') {
            return false !== getenv('ANSICON')
                || 'ON' === getenv('ConEmuANSI')
                || 'xterm' === getenv('TERM');
        }

        return stream_isatty($this->stdout);
    }

    public function hasColorSupport(): bool
    {
        return $this->colorSupport;
    }

    /**
     * 用指定颜色格式化文本
     */
    public function color(string $text, string $color): string
    {
        if (!$this->colorSupport) {
            return $text;
        }
        $code = self::COLORS[$color] ?? '39';
        return "\033[{$code}m{$text}\033[39m";
    }

    /**
     * 解析 <info>text</info> 等样式标签
     */
    public function format(string $text): string
    {
        if (!$this->colorSupport) {
            return preg_replace('/<\/?(?:info|comment|error|warning|debug|success|red|green|yellow|blue|magenta|cyan|white|gray)>/', '', $text);
        }

        return preg_replace_callback('/<(\/)?(info|comment|error|warning|debug|success|red|green|yellow|blue|magenta|cyan|white|gray)>/', function ($matches) {
            if ($matches[1] === '/') {
                return "\033[39m";
            }
            $colorCode = self::STYLES[$matches[2]] ?? self::COLORS[$matches[2]] ?? '39';
            return "\033[{$colorCode}m";
        }, $text);
    }

    // ========== 输出方法 ==========

    /**
     * 写入内容（支持样式标签）
     */
    public function write(string $data)
    {
        return fwrite($this->stdout, $this->format($data));
    }

    /**
     * 写入一行
     */
    public function writeln(string $data, bool $format = true)
    {
        return fwrite($this->stdout, ($format ? $this->format($data) : $data) . PHP_EOL);
    }

    /**
     * 格式化输出
     */
    public function printf(string $fmt, ...$args): int
    {
        return fprintf($this->stdout, $this->format($fmt), ...$args);
    }

    /**
     * 写入错误输出
     */
    public function writeError(string $data)
    {
        return fwrite($this->stderr, $this->format("<error>{$data}</error>"));
    }

    // ========== 快捷样式 ==========

    public function info(string $data)
    {
        return $this->writeln("<info>{$data}</info>");
    }

    public function error(string $data)
    {
        return $this->writeError($data);
    }

    public function warning(string $data)
    {
        return $this->writeln("<warning>{$data}</warning>");
    }

    public function success(string $data)
    {
        return $this->writeln("<success>{$data}</success>");
    }

    public function debug(string $data)
    {
        return $this->writeln("<debug>{$data}</debug>");
    }

    // ========== 详细级别输出 ==========

    /**
     * verbose >= 1 时输出
     */
    public function writelnV(string $data)
    {
        if ($this->verbose >= 1) {
            return $this->writeln($data);
        }
        return 0;
    }

    /**
     * verbose >= 2 时输出
     */
    public function writelnVV(string $data)
    {
        if ($this->verbose >= 2) {
            return $this->writeln($data);
        }
        return 0;
    }

    /**
     * verbose >= 3 时输出（调试）
     */
    public function writelnVVV(string $data)
    {
        if ($this->verbose >= 3) {
            return $this->writeln($data);
        }
        return 0;
    }

    // ====== 兼容旧方法名 ======

    /** @deprecated 使用 writelnV() */
    public function printlnV(string $fmt, ...$args): int
    {
        if ($this->verbose >= 1) {
            return fprintf($this->stdout, $this->format($fmt) . PHP_EOL, ...$args);
        }
        return 0;
    }

    /** @deprecated 使用 writelnVV() */
    public function printlnVV(string $fmt, ...$args): int
    {
        if ($this->verbose >= 2) {
            return fprintf($this->stdout, $this->format($fmt) . PHP_EOL, ...$args);
        }
        return 0;
    }

    /** @deprecated 使用 writelnVVV() */
    public function printlnVVV(string $fmt, ...$args): int
    {
        if ($this->verbose >= 3) {
            return fprintf($this->stdout, $this->format($fmt) . PHP_EOL, ...$args);
        }
        return 0;
    }
}
