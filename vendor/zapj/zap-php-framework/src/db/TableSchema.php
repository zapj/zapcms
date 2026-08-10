<?php

namespace zap\db;

use zap\DB;

/**
 * CREATE TABLE schema builder.
 *
 * Generates and executes DDL statements with driver-specific
 * SQL generation for MySQL, PostgreSQL, and SQLite.
 *
 * @example
 *   Schema::create('articles', function (TableSchema $t) {
 *       $t->increments('id');
 *       $t->varchar('title', 200)->nullable()->comment('标题');
 *       $t->text('content')->comment('内容');
 *       $t->timestamps();
 *   });
 */
class TableSchema
{
    // ─── Engine constants (used for MySQL; ignored by SQLite / PgSQL) ───
    const ENGINE_INNODB = 'InnoDB';
    const ENGINE_MYISAM = 'MyISAM';
    const ENGINE_MEMORY = 'MEMORY';
    const ENGINE_ARCHIVE = 'ARCHIVE';

    /** @var string[] Collected DDL statements for batch execution */
    private array $sql = [];

    protected string $tableName;

    protected string $driver;

    protected string $charset = 'utf8mb4';

    protected string $engine = 'InnoDB';

    protected bool $verbose = false;

    public function __construct(string $tableName, bool $verbose = false)
    {
        $pdo          = DB::getPDO();
        $this->driver = $pdo->driver;

        $this->tableName = $pdo->quoteTable($tableName);
        $this->verbose   = $verbose;

        $this->sql[] = "CREATE TABLE {$this->tableName} (";
    }

    // ─── Column Types ────────────────────────────────────────────

    /**
     * Auto-incrementing primary key.
     */
    public function increments(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'integer')->autoIncrement()->unsigned();
    }

    /**
     * Auto-incrementing bigint primary key.
     */
    public function bigIncrements(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'bigint')->autoIncrement()->unsigned();
    }

    public function varchar(string $column, int $length = 255): ColumnSchema
    {
        return $this->makeColumn($column, 'varchar')->length($length);
    }

    public function string(string $column, int $length = 255): ColumnSchema
    {
        return $this->makeColumn($column, 'varchar')->length($length);
    }

    public function integer(string $column, int $length = 11): ColumnSchema
    {
        return $this->makeColumn($column, 'integer')->length($length);
    }

    public function bigint(string $column, int $length = 20): ColumnSchema
    {
        return $this->makeColumn($column, 'bigint')->length($length);
    }

    public function tinyint(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'tinyint');
    }

    public function smallint(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'smallint');
    }

    public function boolean(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'boolean');
    }

    public function decimal(string $column, int $length = 10): ColumnSchema
    {
        return $this->makeColumn($column, 'decimal')->length($length);
    }

    public function text(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'text');
    }

    public function mediumtext(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'mediumtext');
    }

    public function longtext(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'longtext');
    }

    public function blob(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'blob');
    }

    public function datetime(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'datetime');
    }

    public function date(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'date');
    }

    public function time(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'time');
    }

    public function timestamp(string $column): ColumnSchema
    {
        return $this->makeColumn($column, 'timestamp');
    }

    /**
     * Shorthand for created_at / updated_at columns.
     *
     * @param bool $withTimestamp  Include default CURRENT_TIMESTAMP on created_at
     */
    public function timestamps(bool $withTimestamp = true): void
    {
        $c = $this->makeColumn('created_at', 'timestamp')->nullable();
        if ($withTimestamp) {
            $c->default('CURRENT_TIMESTAMP');
        }
        $this->commitColumn();

        $this->makeColumn('updated_at', 'timestamp')->nullable();
        $this->commitColumn();
    }

    public function softDeletes(string $column = 'deleted_at'): void
    {
        $this->makeColumn($column, 'timestamp')->nullable();
        $this->commitColumn();
    }

    // ─── Index & Constraint Management ───────────────────────────

    /**
     * @param string|string[] $column
     */
    public function addIndex(string $indexName, $column): void
    {
        $columns = $this->normaliseColumns($column);
        $this->sql[] = "INDEX {$indexName}({$columns})";
    }

    public function addForeign(string $indexName, string $column, string $refTable, string $refColumn,
                               string $onDelete = 'cascade', string $onUpdate = 'cascade'): void
    {
        $pdo      = DB::getPDO();
        $refTable = $pdo->quoteTable($refTable);
        $this->sql[] = "CONSTRAINT {$indexName} FOREIGN KEY ({$column}) REFERENCES {$refTable}({$refColumn})"
            . " ON DELETE " . strtoupper($onDelete)
            . " ON UPDATE " . strtoupper($onUpdate);
    }

    public function dropIndex(string $indexName): void
    {
        DB::getPDO()->rawExec("ALTER TABLE {$this->tableName} DROP INDEX {$indexName};");
    }

    /**
     * Add primary key constraint.
     *
     * @param string|string[] $columns
     */
    public function addPrimaryKey($columns): void
    {
        $cols = $this->normaliseColumns($columns);
        $this->sql[] = "PRIMARY KEY ({$cols})";
    }

    // ─── Table Options ───────────────────────────────────────────

    public function setTableEngine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    /**
     * Set character set for the table (MySQL only).
     */
    public function setCharset(string $charset = 'utf8mb4'): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Build and execute the CREATE TABLE statement.
     */
    public function execute(): bool
    {
        $tableCreateSql = $this->toSql();
        return $this->exec($tableCreateSql);
    }

    /**
     * Generate the complete CREATE TABLE SQL string.
     */
    public function toSql(): string
    {
        $this->sql[count($this->sql) - 1] .= rtrim(end($this->sql), ',');

        // Build table engine / charset clause
        if ($this->driver === 'mysql') {
            $this->sql[] = ") ENGINE={$this->engine} DEFAULT CHARSET={$this->charset};";
        } else {
            $this->sql[] = ');';
        }

        return implode("\n", $this->sql);
    }

    // ─── Internals ───────────────────────────────────────────────

    protected function makeColumn(string $name, string $type): ColumnSchema
    {
        $column = new ColumnSchema($name, $type, $this->driver);
        $this->sql[] = $column;
        return $column;
    }

    protected function commitColumn(): void
    {
        $lastIdx = count($this->sql) - 1;
        if ($lastIdx < 0) {
            return;
        }
        $this->sql[$lastIdx] .= ',';
    }

    private function exec(string $sql): bool
    {
        if ($this->verbose) {
            echo $sql . PHP_EOL;
        }
        return DB::getPDO()->rawExec($sql);
    }

    /**
     * @param string|string[] $column
     */
    private function normaliseColumns($column): string
    {
        if (is_string($column)) {
            return $column;
        }
        return implode(',', $column);
    }
}
