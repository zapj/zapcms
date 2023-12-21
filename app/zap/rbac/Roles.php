<?php

namespace zap\rbac;

use zap\db\Model;

class Roles extends Model
{

    public static function tableName(): string
    {
        return 'roles';
    }

    public static function primaryKey(): string
    {
        return 'role_id';
    }
}