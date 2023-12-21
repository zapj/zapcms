<?php

namespace zap\rbac;

use zap\db\Model;

class RolesPermissions extends Model
{

    public static function tableName(): string
    {
        return 'roles_permissions';
    }

    public static function primaryKey()
    {
        return ['role_id','perm_id'];
    }

    public static function getByRoleId($id): array
    {
        $rows = static::find(['role_id'=>$id])->select('perm_key,extras')
            ->fetchAll(FETCH_ASSOC);
        $role_permissions = [];
        foreach ($rows as $row){
            if(!empty($row['extras'])){
                $role_permissions[$row['perm_key']] = array_fill_keys(explode(',',$row['extras']),true);
            }else{
                $role_permissions[$row['perm_key']] = [];
            }

        }
        return $role_permissions;
    }
}