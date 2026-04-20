<?php

namespace zap\facades;

/**
 * @method static setTimeZone($timezone = 'UTC')
 * @method static \DateTime create($datetime,$timezone = null)
 * @method static \DateTime now($timezone = null)
 * @method static string format($format,$datetime,$timezone = null)
 * @method static \DateInterval diff($datetime1,$datetime2)
 *
 */
class Date extends Facade
{
    const NAME = '_dateHelper';

    /**
     * @return \zap\util\Date
     */
    protected static function getInstance()
    {
        $app = app();
        if(!isset($app[static::NAME])){
            $app[static::NAME] = new \zap\util\Date();
        }

        return $app[static::NAME];
    }
}