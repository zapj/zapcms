<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\commands;

use zap\db\Schema;
use zap\console\Args;
use zap\console\Command;
use zap\console\Output;

/**
 * php console zap:DropTable -c sqlite
 */
class DropTable extends Command
{
    function execute(Args $input, Output $out): int
    {
        if($input->hasParam('h')){
            $this->help($out);
        }
        $connection = $input->getParam('c');
        Schema::connection($connection);
        $out->writeln(Schema::drop('user'));
        return self::SUCCESS;
    }

    public function help(Output $out)
    {
        $out->writeln("Help:");
        $out->writeln("-c \tdatabase.php connection name, If not set, defaults to database.default");
        exit(self::SUCCESS);
    }

}