<?php

namespace zap\db;

/**
 * Column definition builder for schema migrations.
 *
 * Supports MySQL, PostgreSQL, and SQLite column types
 * with fluent method chaining.
 *
 * @example
 *   $table->integer('id', 11)->autoIncrement();
 *   $table->varchar('name', 255)->nullable()->default('')->comment('用户名');
 */
class ColumnSchema
{
    protected string $columnName;
    protected string $driver;
    protected string $type;
    protected int $length = 11;
    protected int $decimals = 2;
    protected string $autoIncrement = '';
    protected bool $nullable = false;
    protected string $default = '-Z-NULL';
    protected bool $unsigned = false;
    protected bool $unique = false;
    protected string $comment = '';

    public function __construct(string $name, string $type, string $driver)
    {
        $this->columnName = $name;
        $this->type       = $type;
        $this->driver     = $driver;
    }

    // ─── Fluent Setters ──────────────────────────────────────────

    public function nullable(): self
    {
        $this->nullable = true;
        return $this;
    }

    public function autoIncrement(): self
    {
        if ($this->driver === 'sqlite') {
            $this->autoIncrement = ' PRIMARY KEY AUTOINCREMENT';
        } else {
            $this->autoIncrement = ' AUTO_INCREMENT';
        }
        return $this;
    }

    public function length(int $length): self
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @param mixed $value
     */
    public function default($value): self
    {
        $this->default = $value;
        return $this;
    }

    public function unsigned(): self
    {
        $this->unsigned = true;
        return $this;
    }

    public function unique(): self
    {
        $this->unique = true;
        return $this;
    }

    public function decimals(int $decimals): self
    {
        $this->decimals = $decimals;
        return $this;
    }

    public function comment(string $text): self
    {
        $this->comment = $text;
        return $this;
    }

    // ─── Internal Getters / Setters ──────────────────────────────

    public function getAutoIncrement(): string
    {
        return $this->autoIncrement;
    }

    public function setAutoIncrement(string $value): void
    {
        // Extra leading space intentionally kept for concatenation with column SQL
        $this->autoIncrement = " {$value}";
    }

    // ─── SQL Generation ──────────────────────────────────────────

    public function __toString(): string
    {
        $type     = $this->columnDataType();
        $nullable = $this->nullable ? '' : ' NOT NULL ';
        $unique   = $this->unique ? ' UNIQUE ' : '';
        $comment  = $this->buildComment();

        if ($this->driver === 'sqlite') {
            return "{$this->columnName} {$type}{$nullable}{$this->default}{$unique}{$this->autoIncrement}";
        }
        return "{$this->columnName} {$type}{$nullable}{$this->default}{$unique}{$this->autoIncrement}{$comment}";
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getColumnName(): string
    {
        return $this->columnName;
    }

    public function hasAutoPk(): bool
    {
        return strpos($this->autoIncrement, 'PRIMARY KEY') !== false;
    }

    // ─── Data Type Mapping ───────────────────────────────────────

    public function columnDataType(): string
    {
        $this->resolveDefault();

        if ($this->driver === 'sqlite') {
            return $this->sqliteDataType();
        }
        if ($this->driver === 'pgsql') {
            return $this->pgsqlDataType();
        }
        return $this->mysqlDataType();
    }

    private function resolveDefault(): void
    {
        if ($this->default === '-Z-NULL') {
            $this->default = '';
            return;
        }

        if ($this->default === null) {
            $this->default = ' DEFAULT NULL ';
            return;
        }

        $this->default = " DEFAULT '{$this->default}' ";
    }

    private function buildComment(): string
    {
        if ($this->comment === '') {
            return '';
        }
        return " COMMENT '{$this->comment}'";
    }

    private function sqliteDataType(): string
    {
        switch ($this->type) {
            case 'longtext':
            case 'text':
            case 'varchar':
            case 'char':
            case 'tinytext':
            case 'mediumtext':
                return 'TEXT';
            case 'blob':
                return 'BLOB';
            case 'integer':
            case 'smallint':
                return 'INTEGER';
            case 'tinyint':
                return 'TINYINT';
            case 'bigint':
                return 'BIGINT';
            case 'decimal':
                return "DECIMAL({$this->length},{$this->decimals})";
            case 'timestamp':
            case 'datetime':
                return 'TIMESTAMP';
            default:
                return 'INT';
        }
    }

    private function pgsqlDataType(): string
    {
        switch ($this->type) {
            case 'longtext':
                return 'LONGTEXT';
            case 'text':
                return 'TEXT';
            case 'varchar':
                return "VARCHAR({$this->length})";
            case 'bigint':
                return "BIGINT({$this->length}){$this->autoIncrement}";
            case 'decimal':
                return "DECIMAL({$this->length},{$this->decimals})";
            default:
                return 'INTEGER';
        }
    }

    private function mysqlDataType(): string
    {
        $unsigned = $this->unsigned ? ' UNSIGNED' : '';

        switch ($this->type) {
            case 'longtext':
            case 'text':
            case 'mediumtext':
                return strtoupper($this->type);
            case 'varchar':
                return "VARCHAR({$this->length})";
            case 'tinyint':
                return "TINYINT{$unsigned}";
            case 'smallint':
                return "SMALLINT{$unsigned}";
            case 'bigint':
                return "BIGINT({$this->length}){$unsigned}{$this->autoIncrement}";
            case 'integer':
                return "INT({$this->length}){$unsigned}{$this->autoIncrement}";
            case 'decimal':
                return "DECIMAL({$this->length},{$this->decimals}){$unsigned}";
            case 'boolean':
                return "TINYINT(1)";
            case 'datetime':
                return 'DATETIME';
            case 'date':
                return 'DATE';
            case 'timestamp':
                return 'TIMESTAMP';
            case 'time':
                return 'TIME';
            default:
                return "{$this->type}({$this->length}){$unsigned}{$this->autoIncrement}";
        }
    }
}
