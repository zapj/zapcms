<?php
/*
 * Copyright (c) 2025.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2025/5/6 15:25
 * @lastModified 2025/5/6 15:25
 *
 */

namespace zapcms\support;

use zap\DB;

class SysInfo
{
    public static function getDatabaseTableNames() {
        if(DB::connection()->driver == 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type ='table' AND name NOT LIKE 'sqlite_%'");
        }else{
            $tables = DB::select("SHOW TABLES");
        }

        return $tables;
    }
}