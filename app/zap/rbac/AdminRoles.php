<?php

namespace zap\rbac;

use zap\db\Model;

class AdminRoles extends Model
{

    public static function tableName(): string
    {
        return 'admin_roles';
    }

    public static function primaryKey(): array
    {
        return ['admin_id','role_id'];
    }


}