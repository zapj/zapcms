<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\commands;

use zap\db\Schema;
use zap\console\Command;
use zap\console\Output;

/**
 * php console zap:DropTable -c sqlite
 */
class DropTable extends Command
{
    function execute(): int
    {
        Schema::verbose($this->input->hasParam('v'));
        $connection = $this->input->getParam('c');
        Schema::connection($connection);
        $this->out->writeln(Schema::drop('user'));
        return self::SUCCESS;
    }

    public function help(): int
    {
        $this->out->writeln("Help:");
        $this->out->writeln("-c \tdatabase.php connection name, If not set, defaults to database.default");
        return self::SUCCESS;
    }

}