<?php


return [

    'default' => 'mysql',



    'connections' => [

        'mysql' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => 'zap_cms',
            'username' => 'root',
            'password' => 'root',
//            'unix_socket' => '/tmp/mysql.sock',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => 'zap_',
            'options' => [],
        ],

    ],



];
