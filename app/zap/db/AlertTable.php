<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\db;

use zap\DB;

class AlertTable
{
    protected string $table;
    protected string $tableName;
    protected string $driver;
    protected array $sqlScripts = [];
    public function __construct($table,$connection = null)
    {
        $this->table = DB::getPDO($connection)->quoteTable($table);
        $this->tableName = $table;
        $this->driver = DB::getPDO($connection)->driver;
    }

    public function addColumn($name,$type)
    {
        switch ($this->driver){
            case 'mysql':
                $this->sqlScripts[] = "ALTER TABLE {$this->table} ADD {$name} {$type};";
                break;
            case 'pgsql':
                $this->sqlScripts[] = "ALTER TABLE {$this->table} ADD COLUMN {$name} {$type} constraint;";
                break;
        }
    }

    /**
     * 删除列
     * @param string $name 列名称
     * @return void
     */
    public function removeColumn(string $name)
    {
        switch ($this->driver){
            case 'mysql':
            case 'pgsql':
                $this->sqlScripts[] = "ALTER TABLE {$this->table} DROP COLUMN {$name};";
                break;
        }
    }

    /**
     * 修改列名称和类型
     * MYSQL
     * @return void
     */
    public function changeColumn($name,$new_name,$type,$nullable = false)
    {
        switch ($this->driver){
            case 'mysql':
                $nullable = $nullable ? '' : 'NOT NULL';
                $this->sqlScripts[] = "ALTER TABLE {$this->table} CHANGE {$name} {$new_name} {$type} {$nullable};";
                break;
            case 'pgsql':
//                $this->sqlScripts[] = "ALTER TABLE {$this->table} ADD COLUMN {$name} {$type} constraint;";
                break;
        }
    }

    /**
     * 修改列类型
     * MYSQL
     * @return void
     */
    public function modifyColumn($name,$type,$nullable = false)
    {
        switch ($this->driver){
            case 'mysql':
                $nullable = $nullable ? '' : 'NOT NULL';
                $this->sqlScripts[] = "ALTER TABLE {$this->table} MODIFY {$name} {$type} {$nullable};";
                break;
            case 'pgsql':
                $this->sqlScripts[] = "ALTER TABLE {$this->table} ALTER COLUMN {$name} TYPE {$type};";
                break;
        }
    }

    public function renameColumn($name,$new_name)
    {
        switch ($this->driver){
            case 'mysql':
            case 'pgsql':
                $this->sqlScripts[] = "ALTER TABLE {$this->table} RENAME COLUMN {$name} TO {$new_name};";
                break;
        }
    }

    public function removePrimaryKey($pkName = null)
    {
        switch ($this->driver){
            case 'mysql':
                $this->sqlScripts[] = "DROP INDEX `PRIMARY` ON {$this->table};";
                break;
            case 'pgsql':
                $this->sqlScripts[] = "ALTER TABLE {$this->table} DROP {$pkName};";
                break;
        }
    }

    public function addIndex($constraint_name,...$columns)
    {
        $columns = join(',',$columns);
        switch ($this->driver){
            case 'mysql':
                $this->sqlScripts[] = "ALTER TABLE {$this->table} ADD INDEX {$constraint_name} ({$columns});";
                break;
            case 'pgsql':
                $this->sqlScripts[] = "CREATE INDEX  {$constraint_name} ON {$this->table} ({$columns});";
                break;
        }
    }

    /**
     * @param int $seq
     * @param string|null $seqName pg数据库 必须指定seq name
     * @return void
     */
    public function autoIncrement(int $seq, string $seqName = null)
    {
        if($this->driver == 'mysql'){
            $this->sqlScripts[] = "ALTER TABLE {$this->table} AUTO_INCREMENT = {$seq};";
        }else if($this->driver == 'pgsql' && $seqName != null){
            $this->sqlScripts[] = "ALTER SEQUENCE {$seqName} RESTART WITH {$seq};";
        }
    }

    public function setComment($text)
    {
        if($this->driver == 'mysql'){
            $this->sqlScripts[] = "ALTER TABLE {$this->table} COMMENT  = '{$text}';";
        }else if($this->driver){
            $text = $text === null ? 'NULL' : "'{$text}'";
            $this->sqlScripts[] = "COMMENT ON TABLE {$this->table} IS {$text};";
        }
    }

    public function setEngine($engine)
    {
        if($this->driver == 'mysql'){
            $this->sqlScripts[] = "ALTER TABLE {$this->table} ENGINE = {$engine};";
        }
    }

    public function toSql(): string
    {
        return join("\r\n",$this->sqlScripts);
    }
}