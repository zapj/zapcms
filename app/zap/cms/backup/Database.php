<?php
/*
 * Copyright (c) 2025.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2025/5/7 16:56
 * @lastModified 2025/5/7 16:56
 *
 */

namespace app\zap\cms\backup;

use zap\DB;

class Database
{
    /**
     * 备份数据库表结构及数据
     * @return bool
     */
    public static function backup()
    {

        $conn_name = config('database.default');
        $dbname = config("database.connections.{$conn_name}.dbname");
        $driver = config("database.connections.{$conn_name}.driver");
        if($driver === 'sqlite'){ $dbname = "zapcms"; }
        try {
            $tables = DB::getTables();
            $backupDir = var_path('backups/sql');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            $fileName = $backupDir . '/' . $dbname . '_' . date('YmdHis') . '.sql';
            if(file_exists($fileName) === true){
                file_put_contents($fileName, '');
            }
            foreach ($tables as $table) {
                $structure = DB::getTableStructure($table);
                $sql = "DROP TABLE IF EXISTS `$table`;\n";
                $sql .= $structure . ";\n\n";
                $query = DB::query("select * from {$table}");
                while(($row = $query->fetch(\PDO::FETCH_ASSOC)) !== false ) {
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }
                    $sql .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }

                // Save to file

                file_put_contents($fileName, $sql, FILE_APPEND);
            }

            return true;
        } catch (\Exception $e) {
            print_r($e);
            return false;
        }
    }

    public static function backupData(){

    }

    public static function restore(){

    }

    public static function restoreData(){

    }
}