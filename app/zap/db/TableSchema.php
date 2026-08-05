<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\db;

use zap\DB;

class TableSchema
{
    const ENGINE_INNODB = 'InnoDB';
    const ENGINE_MYISAM = 'MyISAM';
    protected string $table;
    protected string $tableName;
    protected array $columns;
    public string $driver;
    protected string $primaryKeys = '';
    protected string $uniqueKeys  = '';
    protected string $indexKeys  = '';

    protected bool $existsDropTable = false;

    private string $tableEngine = 'InnoDB';

    public function __construct($table,$connection = null)
    {
        $this->table = DB::getPDO($connection)->quoteTable($table);
        $this->tableName = $table;
        $this->columns = [];
        $this->driver = DB::getPDO($connection)->driver;
    }

    public function setTableEngine($engine)
    {
        $this->tableEngine = $engine;
    }

    public function tinyint($name): ColumnSchema
    {
        $column = new ColumnSchema($name,'tinyint',$this->driver);
        $this->columns[] = $column;
        return $column;
    }

    public function integer($name,$length = 11): ColumnSchema
    {
        $column = new ColumnSchema($name,'integer',$this->driver);
        $this->columns[] = $column;
        $column->length($length);
        return $column;
    }

    public function bigint($name,$length = 11): ColumnSchema
    {
        $column = new ColumnSchema($name,'bigint',$this->driver);
        $this->columns[] = $column;
        $column->length($length);
        return $column;
    }

    public function char($name,$length): ColumnSchema
    {
        $column = new ColumnSchema($name,'char',$this->driver);
        $this->columns[] = $column;
        $column->length($length);
        return $column;
    }

    public function varchar($name,$length): ColumnSchema
    {
        $column = new ColumnSchema($name,'varchar',$this->driver);
        $this->columns[] = $column;
        $column->length($length);
        return $column;
    }

    public function text($name): ColumnSchema
    {
        $column = new ColumnSchema($name,'text',$this->driver);
        $this->columns[] = $column;
        return $column;
    }

    public function longtext($name): ColumnSchema
    {
        $column = new ColumnSchema($name,'longtext',$this->driver);
        $this->columns[] = $column;
        return $column;
    }

    public function addColumn($name,$type): ColumnSchema
    {
        $column = new ColumnSchema($name,$type,$this->driver);
        $this->columns[] = $column;
        return $column;
    }

    public function addPrimary($constraint_name,...$columns): TableSchema
    {
        if($this->driver === 'sqlite' && $this->resetAutoIncrementColumn(count($columns))){
            return $this;
        }
        if($constraint_name !== null && $this->driver === 'sqlite'){
            $constraint_name = "constraint \"{$constraint_name}\" ";
        } else if($constraint_name !== null && $this->driver !== 'sqlite'){
            $constraint_name = "CONSTRAINT {$constraint_name} ";
        }
        $columnNames = join(',',$columns);

        $this->primaryKeys = ",\n    {$constraint_name}PRIMARY KEY({$columnNames})";
        return $this;
    }

    public function addUnique($constraint_name,...$columns): TableSchema
    {
        $columnNames = join(',',$columns);
        if($constraint_name !== null && $this->driver === 'mysql'){
            $constraint_name = "CONSTRAINT {$constraint_name} ";
        }

        if($this->driver === 'mysql'){
            $this->uniqueKeys .= ",\n    {$constraint_name}UNIQUE({$columnNames})";
        }else if($this->driver === 'sqlite'){
            $this->uniqueKeys .= "CREATE UNIQUE INDEX {$constraint_name}_index ON {$this->table} ({$columnNames});";
        }
        return $this;
    }

    /**
     * 添加索引
     * @param string|null $constraint_name 索引名称
     * @param ...$columns
     * @return $this
     */
    public function addIndex(?string $constraint_name, ...$columns): TableSchema
    {
        $columnNames = join(',',$columns);
        if($constraint_name !== null && $this->driver === 'mysql'){
            $constraint_name = "{$constraint_name} ";
        }
        if($this->driver === 'mysql'){
            $this->indexKeys .= ",\n    INDEX {$constraint_name}({$columnNames})";
        }else if($this->driver === 'sqlite'){
            $this->indexKeys .= "CREATE INDEX {$this->table}_{$constraint_name}_index ON {$this->table} ({$columnNames});";
        }
        return $this;
    }

    public function dropTableIfExists(): TableSchema
    {
        $this->existsDropTable = true;
        return $this;
    }

    public function toSql(): string
    {
        switch ($this->driver){
            case 'sqlite':
                return $this->sqliteToString();
            case 'pgsql':
                return $this->pgsqlToString();
            case 'mysql':
            default:
                return $this->mysqlToString();
        }
    }

    private function sqliteToString(): string
    {
        $dropTable = $this->existsDropTable ? "DROP TABLE IF EXISTS {$this->table};" : '';
        $columns = join(",\n    ",$this->columns);
        return <<<EOF
{$dropTable}
CREATE TABLE {$this->table} (
    {$columns}{$this->primaryKeys}
);
{$this->uniqueKeys}
{$this->indexKeys}
EOF;

    }

    private function mysqlToString(): string
    {
        $dropTable = $this->existsDropTable ? "DROP TABLE IF EXISTS {$this->table};" : '';
        $columns = join(",\n    ",$this->columns);
        return <<<EOF
{$dropTable}
CREATE TABLE {$this->table} (
    {$columns}{$this->primaryKeys}{$this->uniqueKeys}{$this->indexKeys}
) ENGINE={$this->tableEngine};
EOF;

    }

    private function pgsqlToString(): string
    {
        $columns = join(",\n    ",$this->columns);
        return <<<EOF
CREATE TABLE {$this->table} (
    {$columns}{$this->primaryKeys}{$this->uniqueKeys}{$this->indexKeys}
)
EOF;

    }

    private function resetAutoIncrementColumn($primaryKeyCount): bool
    {
        $exists = false;
        foreach ($this->columns as $column){
            $autoincrement = $column->getAUTOINCREMENT();
            if($autoincrement !== '' ){
                if($primaryKeyCount > 1){
                    $column->setAUTOINCREMENT('AUTOINCREMENT');
                }else{
                    $exists = true;
                }
                break;
            }
        }
        return $exists;
    }

}