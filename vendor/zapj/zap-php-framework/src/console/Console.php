<?php

namespace zap\console;

use zap\App;

class Console
{
    protected App $app;
    protected Input $input;
    protected Output $output;
    protected string $defaultCommand = '\zap\console\DefaultCommand';
    protected array $commands = [];

    public function __construct($appPath)
    {
        $this->app = new App($appPath);
        $this->input = new Input();
        $this->output = new Output($this->input);
        set_exception_handler(null);
        set_error_handler(null);
        register_shutdown_function(function () {
            return false;
        });
    }

    public function setDefaultCommand(string $command): Console
    {
        $this->defaultCommand = $command;
        return $this;
    }

    public function addCommand(string $path, string $namespace): Console
    {
        $this->commands[$path] = $namespace;
        return $this;
    }

    public function getCommands(): array
    {
        return $this->commands;
    }

    // ========== 命令执行 ==========

    public function execute(): int
    {
        // --version / -V
        if ($this->input->hasParam('V') || $this->input->hasParam('version')) {
            $this->output->writeln('<info>Zap Console</info> v1.0.0');
            return Command::SUCCESS;
        }

        $commandName = $this->input->getParam(1);

        // 无参数或 help / list
        if ($commandName === null) {
            return $this->callCommand($this->defaultCommand);
        }

        if (in_array($commandName, ['help', 'list', '-h', '--help'], true)) {
            return $this->callCommand($this->defaultCommand);
        }

        // 解析 vendor:command 格式
        $className = $this->resolveCommandClass($commandName);

        if ($className === null) {
            $this->output->writeln("<error>命令未找到: {$commandName}</error>");
            $this->output->writeln('  运行 <info>php console list</info> 查看可用命令');
            return Command::FAILURE;
        }

        return $this->callCommand($className);
    }

    /**
     * 将命令名解析为完整的类名
     */
    protected function resolveCommandClass(string $commandName): ?string
    {
        // vendor:command 格式
        if (str_contains($commandName, ':')) {
            [$vendor, $name] = explode(':', $commandName, 2);
            $className = "\\{$vendor}\\commands\\" . $name;
            if (class_exists($className)) {
                return $className;
            }
            return null;
        }

        // 直接类名
        if (class_exists($commandName)) {
            return $commandName;
        }

        // 尝试 app\commands 命名空间
        $className = '\\app\\commands\\' . $commandName;
        if (class_exists($className)) {
            return $className;
        }

        return null;
    }

    /**
     * 调用命令实例
     */
    protected function callCommand(string $className): int
    {
        try {
            $reflect = new \ReflectionClass($className);
        } catch (\ReflectionException $e) {
            $this->output->writeln("<error>命令类不存在: {$className}</error>");
            return Command::FAILURE;
        }

        if (!$reflect->isSubclassOf(Command::class)) {
            $this->output->writeln("<error>{$className} 必须继承 " . Command::class . '</error>');
            return Command::FAILURE;
        }

        if ($reflect->isAbstract()) {
            $this->output->writeln("<error>{$className} 是抽象类，无法执行</error>");
            return Command::FAILURE;
        }

        try {
            /** @var Command $cmd */
            $cmd = $reflect->newInstance();
            $cmd->setConsole($this);
            $cmd->setInput($this->input);
            $cmd->setOutput($this->output);
            $cmd->init();

            // 显示帮助
            if ($this->input->hasParam('h') || $this->input->hasParam('help')) {
                return $cmd->help();
            }

            return $cmd->execute($this->input, $this->output);
        } catch (\Throwable $e) {
            $this->output->writeln("<error>命令执行异常: {$e->getMessage()}</error>");
            if ($this->output->getVerbose() >= 1) {
                $this->output->writeln("<debug>{$e->getTraceAsString()}</debug>", false);
            }
            return Command::FAILURE;
        }
    }

    public function getInput(): Input
    {
        return $this->input;
    }

    public function getOutput(): Output
    {
        return $this->output;
    }
}
