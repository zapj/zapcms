<?php

namespace app\zap;

use zap\db\Model;

class AdminLogs extends Model
{

    public static function tableName(): string
    {
        return 'admin_logs';
    }

    public static function primaryKey()
    {
        return 'id';
    }
}