<?php

namespace zap\rbac;

use zap\db\Model;

class RolesPermissions extends Model
{
    protected $table = 'roles_permissions';

    protected $primaryKey = ['role_id','perm_id'];

    public static function tableName(): string
    {
        return 'roles_permissions';
    }
}