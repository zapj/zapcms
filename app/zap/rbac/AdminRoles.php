<?php

namespace zap\rbac;

use zap\db\Model;

class AdminRoles extends Model
{
    protected $table = 'admin_roles';

    protected $primaryKey = ['admin_id','role_id'];

    public static function tableName(): string
    {
        return 'admin_roles';
    }


}