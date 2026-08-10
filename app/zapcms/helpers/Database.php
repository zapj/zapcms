<?php
/*
 * Copyright (c) 2025.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2025/5/7 16:56
 * @lastModified 2025/5/7 16:56
 *
 */

namespace zapcms\helpers;

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
            // 获取所有表名
            if ($driver === 'sqlite') {
                $tableRows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                $tables = array_column($tableRows, 'name');
            } else {
                $tableRows = DB::select("SHOW TABLES");
                $tables = array_map('current', $tableRows);
            }

            // 创建备份目录
            $backupDir = var_path('backups/sql');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0777, true);
            }
            $fileName = $backupDir . '/' . $dbname . '_' . date('YmdHis') . '.sql';

            $fp = fopen($fileName, 'w');
            if (!$fp) {
                throw new \RuntimeException("无法创建备份文件: {$fileName}");
            }

            foreach ($tables as $table) {
                // 获取建表语句
                if ($driver === 'sqlite') {
                    $structRows = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=:name", ['name' => $table]);
                    $structure = $structRows[0]['sql'] ?? '';
                } else {
                    $structRows = DB::select("SHOW CREATE TABLE `{$table}`");
                    $structure = $structRows[0]['Create Table'] ?? '';
                }

                fwrite($fp, "DROP TABLE IF EXISTS `{$table}`;\n");
                fwrite($fp, $structure . ";\n\n");

                // 导出数据
                $query = DB::query("SELECT * FROM `{$table}`");
                while (($row = $query->fetch(\PDO::FETCH_ASSOC)) !== false) {
                    $values = [];
                    foreach ($row as $value) {
                        $values[] = is_null($value) ? 'NULL' : "'" . addslashes($value) . "'";
                    }
                    fwrite($fp, "INSERT INTO `{$table}` VALUES (" . implode(', ', $values) . ");\n");
                }
                fwrite($fp, "\n");
            }

            fclose($fp);
            return true;
        } catch (\Exception $e) {
            trigger_error('数据库备份失败: ' . $e->getMessage(), E_USER_WARNING);
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