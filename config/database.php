<?php


return [

    'default' => 'default',


    'connections' => [

        'default' => [
            'driver' => 'mysql',
//            'dsn' => "mysql:host=127.0.0.1;dbname=world",
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbname' => 'zap_cms',
            'user' => 'root',
            'password' => 'root',
//            'unix_socket' => '/tmp/mysql.sock',
            'charset' => 'utf8',
            'collate' => 'utf8_unicode_ci',
            'prefix' => 'zap_',
            'options' => [],
        ],
//        'sqlite'=>[
//            'driver' => 'sqlite',
//            'prefix' => 'zap_',
//            'dsn'=> sprintf('sqlite:%s', resource_path('zapcms.db'))
//        ]
    ],



];
