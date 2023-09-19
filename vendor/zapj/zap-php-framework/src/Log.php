<?php

namespace zap;

/**
 * Level Log::DEBUG|Log::INFO|Log::NOTICE|Log::WARNING|Log::ERROR|Log::CRITICAL|Log::ALERT|Log::EMERGENCY
 *
 * @method static info($message, array $context = [])
 * @method static warning($message, array $context = [])
 * @method static error($message, array $context = [])
 * @method static debug($message, array $context = [])
 * @method static alert($message, array $context = [])
 * @method static emergency($message, array $context = [])
 * @method static critical($message, array $context = [])
 *
 */
class Log
{

    public const DEBUG = 100;

    public const INFO = 200;

    public const NOTICE = 250;

    public const WARNING = 300;

    public const ERROR = 400;

    public const CRITICAL = 500;

    public const ALERT = 550;

    public const EMERGENCY = 600;


    public static function __callStatic($name, $arguments)
    {
        if(config('config.log',false)) {
            call_user_func_array([app()->getLogger(), $name], $arguments);
        }
    }

}