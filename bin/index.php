<?php

use zap\Console;

require dirname(__DIR__) . "/vendor/autoload.php";

$app = new Console(dirname(__DIR__));
if(!$app->isConsole()){
    die;
}
echo $app->isWin();
