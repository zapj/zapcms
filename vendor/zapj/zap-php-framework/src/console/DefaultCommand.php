<?php

namespace zap\console;

use zap\util\FileUtils;

class DefaultCommand extends Command
{
    public function configure(): void
    {
        $this->setName('list')
             ->setDescription('显示可用命令列表');
    }

    public function execute(Input $input, Output $output): int
    {
        $output->writeln('');
        $output->writeln('  <info>Zap Console</info>');
        $output->writeln('');

        $commands = $this->console->getCommands();
        $commands = empty($commands) ? ['app' => 'app'] : $commands;

        $hasCommands = false;

        foreach ($commands as $path => $prefix) {
            $dir = base_path($path);
            if (!is_dir($dir)) {
                continue;
            }

            // 使用 FilesystemIterator 扫描命令文件
            $iterator = new \FilesystemIterator($dir);
            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $baseName = $file->getBasename('.php');
                $className = "\\{$prefix}\\commands\\" . $baseName;

                if (!class_exists($className)) {
                    continue;
                }

                // 使用反射获取描述，避免实例化
                try {
                    $reflect = new \ReflectionClass($className);
                } catch (\ReflectionException $e) {
                    continue;
                }

                if ($reflect->isAbstract() || !$reflect->isSubclassOf(Command::class)) {
                    continue;
                }

                $displayName = $prefix === 'app' ? $baseName : "{$prefix}:{$baseName}";

                // 尝试从常量或静态属性获取描述
                $description = '';
                if ($reflect->hasMethod('description')) {
                    // 尝试不实例化获取 — 如果 description() 是简单返回字符串则安全
                    $instance = null;
                    try {
                        $instance = $reflect->newInstanceWithoutConstructor();
                        $description = $instance->description();
                    } catch (\Throwable $e) {
                        $description = '';
                    }
                }

                $output->writeln(sprintf('  <success>%-30s</success> %s', $displayName, $description));
                $hasCommands = true;
            }
        }

        if (!$hasCommands) {
            $output->writeln('  <comment>没有找到可用的命令</comment>');
        }

        $output->writeln('');
        $output->writeln(str_repeat('-', 52));
        $output->writeln('  运行 <info>php console &lt;命令名&gt; -h</info> 查看命令详情');
        $output->writeln('');

        return self::SUCCESS;
    }
}
