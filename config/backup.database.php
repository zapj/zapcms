<?php 

//Automatically generated by zap cms
//Date:2023-12-26 14:43:43
return [
  'default' => 'default',
  'connections' => [
    'default' => [
      'driver' => 'sqlite',
      'dsn' => sprintf("sqlite:%s", var_path("data/zapcms.db")),
      'prefix' => 'zap_',
      'charset' => 'utf8',
      'collate' => 'utf8_general_ci',
    ],
  ],
];
