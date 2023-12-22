<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap\db;

class ColumnSchema
{
    protected string $columnName;
    protected string $driver;
    protected string $type;
    protected int $length = 11;
    protected string $AUTO_INCREMENT = '';

    /**
     * @var bool 允许Null
     */
    protected bool $nullable = false;
    protected $default = '-Z-NULL';
    public function __construct($name,$type,$driver)
    {
        $this->columnName = $name;
        $this->driver = $driver;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getAUTOINCREMENT(): string
    {
        return $this->AUTO_INCREMENT;
    }

    /**
     * @param string $AUTO_INCREMENT
     */
    public function setAUTOINCREMENT(string $AUTO_INCREMENT): void
    {
        $this->AUTO_INCREMENT = " {$AUTO_INCREMENT}";
    }

    public function nullable(): ColumnSchema
    {
        $this->nullable = true;
        return $this;
    }

    public function autoIncrement(): ColumnSchema
    {
        switch ($this->driver){
            case 'sqlite':
                $this->AUTO_INCREMENT = ' PRIMARY KEY AUTOINCREMENT';
                break;
            case 'mysql':
            default:
                $this->AUTO_INCREMENT = ' AUTO_INCREMENT';
                break;
        }
        return $this;
    }

    public function length($length): ColumnSchema
    {
        $this->length = $length;
        return $this;
    }

    public function default($value): ColumnSchema
    {
        $this->default = $value;
        return $this;
    }

    public function __toString()
    {
        $nullable = $this->nullable ? '' : ' NOT NULL ';
        $this->type = $this->columnDataType();

        switch ($this->driver){
            case 'pgsql':
                return "{$this->columnName} {$this->type}{$nullable}{$this->default} ";
            case 'sqlite':
                return "{$this->columnName} {$this->type}{$nullable}{$this->default}{$this->AUTO_INCREMENT}";
        }
        return "{$this->columnName} {$this->type}{$nullable}{$this->default}";

    }

    public function columnDataType(): string
    {
        switch ($this->driver){
            case 'sqlite':
                $this->default = $this->default === '-Z-NULL' ? '' : ($this->default === null ? ' DEFAULT NULL ' : " DEFAULT '{$this->default}' ");
                return $this->sqliteDataType();
            case 'pgsql':
                $this->default = $this->default === '-Z-NULL' ? '' : ($this->default === null ? ' DEFAULT NULL ' : " DEFAULT '{$this->default}' ");
                return $this->pgsqlDataType();
            case 'mysql':
            default:
                $this->default = $this->default === '-Z-NULL' ? '' : ($this->default === null ? ' DEFAULT NULL ' : " DEFAULT '{$this->default}' ");
                return $this->mysqlDataType();
        }
    }

    public function sqliteDataType(): string
    {
        switch ($this->type){
            case 'longtext':
                return 'LONGTEXT';
            case 'text':
            case 'varchar':
                return 'TEXT';
            case 'blob':
                return 'BLOB';
            case 'integer':
                return 'INTEGER';
            default:
                return 'INT';
        }
    }

    public function pgsqlDataType(): string
    {
        switch ($this->type){
            case 'longtext':
                return 'LONGTEXT';
            case 'text':
                return 'TEXT';
            case 'varchar':
                return "VARCHAR({$this->length})";
            case 'bigint':
                return "BIGINT({$this->length}){$this->AUTO_INCREMENT}";
            case 'integer':
            default:
                return 'INTEGER';
        }
    }

    public function mysqlDataType(): string
    {
        switch ($this->type){
            case 'longtext':
                return 'LONGTEXT';
            case 'text':
                return 'TEXT';
            case 'varchar':
                return "VARCHAR({$this->length})";
            case 'bigint':
                return "BIGINT({$this->length}){$this->AUTO_INCREMENT}";
            case 'integer':
            default:
                return "INT({$this->length}){$this->AUTO_INCREMENT}";
        }
    }

}