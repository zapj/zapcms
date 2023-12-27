<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author        Allen
 * @email        zapcms@zap.cn
 * @date        2023/12/27 上午11:01
 * @lastModified        2023/12/20 下午6:29
 *
 */

namespace zap\cms\rbac;

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