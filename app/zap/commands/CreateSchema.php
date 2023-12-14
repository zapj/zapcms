<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\commands;

use zap\cms\CreateTables;
use zap\db\Schema;
use zap\console\Command;

/**
 * php console zap:CreateSchema -c sqlite
 */
class CreateSchema extends Command
{
    function execute(): int
    {
        $connection = $this->input->getParam('c','my_test');
        Schema::connection($connection);
        Schema::verbose($this->out->getVerbose());
        $schema = new CreateTables($this->out->getVerbose());
        $schema->createSchema();

        if($this->input->getParam('b')){
            $schema->installBaseData();
        }

        if($this->input->getParam('d')){
            $schema->installDemoData();
        }

        return self::SUCCESS;
    }

    public function help() : int
    {
        $this->out->writeln("ZapCMS 创建表结构");
        $this->out->writeln("Help:");
        $this->out->writeln("-b\t基础数据");
        $this->out->writeln("-v\t显示创建脚本");
        return self::SUCCESS;
    }

}