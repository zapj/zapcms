<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\db;


use Exception;
use zap\console\Output;
use zap\DB;

class Schema
{

    protected static ?string $connection = null;
    /**
     * @var int v length
     */
    protected static int $verbose = 0;

    public static function connection($connection = null)
    {
        static::$connection = $connection;
    }

    public static function verbose($flag = null): int
    {
        if($flag === null) {
            return static::$verbose;
        }
        static::$verbose = $flag;
        return static::$verbose;
    }

    public static function create($table,\Closure $closure)
    {
        $tableSchema = new TableSchema($table,static::$connection);
        $closure($tableSchema);
        $sqlText = $tableSchema->toSql();
        if(static::$verbose === 2){
            echo $sqlText,"\r\n";
        }
        $ret = DB::getPDO(static::$connection)->rawExec($sqlText);
        if($ret !== false){
            echo "Table {$table} : CREATE SUCCESS \r\n";
            return true;
        }
        echo "Table {$table} : CREATE FAILED \r\n";
        return false;
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
        if($sqlText === null){
            return false;
        }
        if(static::$verbose === true){
            echo $sqlText,"\r\n";
        }
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