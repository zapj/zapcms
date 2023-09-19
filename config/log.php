<?php

return [
    "default"=> "app",
    "app"=>[
        "handler"=>\Monolog\Handler\RotatingFileHandler::class,
        "params"=>[
            'filename'=> var_path('logs/app.log')
        ]
    ]
];

