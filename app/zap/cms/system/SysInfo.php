<?php
/*
 * Copyright (c) 2025.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2025/5/6 15:25
 * @lastModified 2025/5/6 15:25
 *
 */

namespace app\zap\cms\system;

use zap\DB;

class SysInfo
{
    public static function getDatabaseTableNames() {
        if(DB::getPDO()->driver == 'sqlite') {
            $tables = DB::query("SELECT name FROM sqlite_master WHERE type ='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(FETCH_ASSOC);
        }else{
            $tables = DB::query("SHOW TABLES")->fetchAll(FETCH_ASSOC);
        }

        return $tables;
    }
}