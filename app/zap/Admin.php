<?php

namespace zap;

use zap\db\Model;

class Admin extends Model
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

    public static function getProfile($id = null){
        $id = $id ?? Auth::user('id');
        return DB::table(static::tableName())->where('id',$id)->fetch(FETCH_ASSOC);
    }

}