<?php

return [
    "default"=> "app",
    "app"=>[
        "handler"=>\Monolog\Handler\RotatingFileHandler::class,
        "params"=>[
            'filename'=> storage_path('/logs/app.log')
        ]
    ]
];

