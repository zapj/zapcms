<?php

namespace zap\rbac;

use zap\db\Model;

class Roles extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'role_id';

    public static function tableName(): string
    {
        return 'roles';
    }
}