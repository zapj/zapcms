<?php

namespace zap\console;

abstract class Command
{
    const SUCCESS = 0;
    const FAILURE = 1;

    protected Input $input;
    protected Output $out;
    protected Console $console;

    protected string $name = '';
    protected string $description = '';
    protected array $arguments = [];
    protected array $options = [];

    // ========== Setter / Getter ==========

    public function setConsole(Console $console): void
    {
        $this->console = $console;
    }

    public function getConsole(): Console
    {
        return $this->console;
    }

    public function getInput(): Input
    {
        return $this->input;
    }

    public function setInput(Input $input): void
    {
        $this->input = $input;
    }

    public function getOutput(): Output
    {
        return $this->out;
    }

    public function setOutput(Output $out): void
    {
        $this->out = $out;
    }

    // ========== 命令配置 ==========

    /**
     * 配置命令：名称、描述、参数、选项
     * 子类覆盖此方法
     */
    public function configure(): void
    {
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function description(): string
    {
        return $this->description;
    }

    /**
     * 添加位置参数
     *
     * @param string $name        参数名
     * @param string $description 参数描述
     * @param bool   $required    是否必填
     * @param mixed  $default     默认值
     */
    public function addArgument(string $name, string $description = '', bool $required = false, $default = null)
    {
        $this->arguments[$name] = compact('description', 'required', 'default');
        return $this;
    }

    /**
     * @return array<string, array>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * 添加命名选项
     *
     * @param string $name        选项名
     * @param string $shortcut    短选项（如 'v'）
     * @param string $description 描述
     * @param mixed  $default     默认值
     */
    public function addOption(string $name, string $shortcut = '', string $description = '', $default = null)
    {
        $this->options[$name] = compact('shortcut', 'description', 'default');
        return $this;
    }

    /**
     * @return array<string, array>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    // ========== 生命周期 ==========

    public function init(): void
    {
        $this->configure();
    }

    public function help(): int
    {
        $this->out->writeln("<info>{$this->name}</info>");
        if ($this->description) {
            $this->out->writeln("  {$this->description}");
        }
        $this->out->writeln('');

        // 用法
        $usage = "  <comment>用法:</comment> {$this->name}";
        foreach ($this->arguments as $argName => $arg) {
            $usage .= $arg['required'] ? " <{$argName}>" : " [{$argName}]";
        }
        foreach ($this->options as $optName => $opt) {
            $shortcut = $opt['shortcut'] ? "|-{$opt['shortcut']}" : '';
            $usage .= " [--{$optName}{$shortcut}]";
        }
        $this->out->writeln($usage);

        // 参数
        if (!empty($this->arguments)) {
            $this->out->writeln('');
            $this->out->writeln('  <comment>参数:</comment>');
            foreach ($this->arguments as $argName => $arg) {
                $required = $arg['required'] ? '<info>*</info>' : '';
                $this->out->writeln(sprintf("    %-20s %s  %s", $argName, $required, $arg['description']));
            }
        }

        // 选项
        if (!empty($this->options)) {
            $this->out->writeln('');
            $this->out->writeln('  <comment>选项:</comment>');
            foreach ($this->options as $optName => $opt) {
                $short = $opt['shortcut'] ? "-{$opt['shortcut']}, " : '    ';
                $this->out->writeln(sprintf("    %s--%-16s %s", $short, $optName, $opt['description']));
            }
        }

        return self::SUCCESS;
    }

    /**
     * 执行命令
     */
    abstract public function execute(Input $input, Output $output): int;
}
