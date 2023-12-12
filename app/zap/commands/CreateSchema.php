<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\commands;

use zap\cmschema\CreateTables;
use zap\db\Schema;
use zap\db\TableSchema;
use zap\console\Args;
use zap\console\Command;
use zap\console\Output;

/**
 * php console zap:CreateSchema -c sqlite
 */
class CreateSchema extends Command
{
    function execute(Args $input, Output $out): int
    {
        $connection = $input->getParam('c','my_test');
        Schema::connection($connection);
        $schema = new CreateTables();
        $schema->createSchema();


        return self::SUCCESS;
    }

}