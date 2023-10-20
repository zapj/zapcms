<?php

namespace zap;

use zap\db\Model;

class User extends Model
{

    protected $table = 'admin';

    protected $primaryKey = 'id';

    const STATUS_ACTIVATED = 'activated';
    const STATUS_DISABLED = 'disabled';


    public static function tableName(): string
    {
        return 'admin';
    }

    public static function getStatusTitle($status): string
    {
        switch ($status){
            case self::STATUS_ACTIVATED:
                return '可用';
            default:
                return '禁用';
        }
    }

    public static function getStatus(): array
    {
        return [
            self::STATUS_ACTIVATED => '可用',
            self::STATUS_DISABLED => '禁用',

        ];
    }


}