<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\db;


use Exception;
use zap\DB;
use zap\db\AlertTable;

class Schema
{

    protected static ?string $connection = null;

    public static function connection($connection = null)
    {
        static::$connection = $connection;
    }

    public static function create($table,\Closure $closure)
    {
        $tableSchema = new TableSchema($table,static::$connection);
        $closure($tableSchema);
        $sqlText = $tableSchema->toSql();
        echo $sqlText,"\r\n";
        return DB::getPDO(static::$connection)->rawExec($sqlText);
    }

    /**
     * 修改表
     * @param $table
     * @param \Closure $closure
     * @return false|int
     * @throws Exception
     */
    public static function table($table,\Closure $closure)
    {
        $alertTable = new AlertTable($table,static::$connection);
        $closure($alertTable);
        $sqlText = $alertTable->toSql();
        echo $sqlText,"\r\n";
        return DB::getPDO(static::$connection)->rawExec($sqlText);

    }

    /**
     * 删除表
     * @param $table
     * @return string
     * @throws Exception
     */
    public static function drop($table): string
    {
        try {
            DB::getPDO(static::$connection)->dropTable($table);
        }catch (\PDOException $e){
            return "DROP TABLE {$table} : {$e->getMessage()}";
        }
        return "DROP TABLE {$table} : SUCCESS";
    }

    /**
     * 重命名表
     * @param string $table 旧表面
     * @param string $to 新表明
     * @return string
     * @throws Exception
     */
    public static function rename(string $table,string $to): string
    {
        try {
            DB::getPDO(static::$connection)->renameTable($table,$to);
        }catch (\PDOException $e){
            return "RENAME TABLE {$table} : {$e->getMessage()}";
        }
        return "RENAME TABLE {$table} : SUCCESS";
    }

}