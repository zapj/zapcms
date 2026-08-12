<?php

namespace zap\db;

use zap\DB;

/**
 * ALTER TABLE schema builder.
 *
 * Handles column add/remove/modify/rename, index management,
 * and data seeding (insert / batch insert).
 *
 * @example
 *   Schema::alter('articles', function (AlertTable $t) {
 *       $t->varchar('slug', 100)->nullable()->comment('别名');
 *       $t->addIndex('idx_slug', 'slug');
 *       $t->insert(['name' => 'default', 'slug' => 'default']);
 *   });
 */
class AlertTable
{
    /** @var string[] DDL statements */
    private array $sql = [];

    /** @var array DML statements (inserts) */
    private array $dataSql = [];

    protected string $tableName;

    protected string $driver;

    protected bool $verbose = false;

    public function __construct(string $tableName, bool $verbose = false)
    {
        $pdo          = DB::getPDO();
        $this->driver = $pdo->driver;

        $this->tableName = $pdo->quoteTable($tableName);
        $this->verbose   = $verbose;
    }

    // ─── Column Types (add) ──────────────────────────────────────

    public function varchar(string $column, int $length = 255): ColumnSchema
    {
        return $this->addedColumn($column, 'varchar')->length($length);
    }

    public function string(string $column, int $length = 255): ColumnSchema
    {
        return $this->varchar($column, $length);
    }

    public function integer(string $column, int $length = 11): ColumnSchema
    {
        return $this->addedColumn($column, 'integer')->length($length);
    }

    public function bigint(string $column, int $length = 20): ColumnSchema
    {
        return $this->addedColumn($column, 'bigint')->length($length);
    }

    public function smallint(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'smallint');
    }

    public function tinyint(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'tinyint');
    }

    public function boolean(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'boolean');
    }

    public function decimal(string $column, int $length = 10, int $decimals = 2): ColumnSchema
    {
        return $this->addedColumn($column, 'decimal')->length($length)->decimals($decimals);
    }

    public function text(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'text');
    }

    public function longtext(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'longtext');
    }

    public function mediumtext(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'mediumtext');
    }

    public function datetime(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'datetime');
    }

    public function date(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'date');
    }

    public function timestamp(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'timestamp');
    }

    public function time(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'time');
    }

    public function blob(string $column): ColumnSchema
    {
        return $this->addedColumn($column, 'blob');
    }

    // ─── Column Operations ───────────────────────────────────────

    /**
     * Drop a column from the table.
     */
    public function removeColumn(string $column): void
    {
        $columnName = DB::getPDO()->quoteColumn($column);
        $this->sql[] = "DROP COLUMN {$columnName}";
    }

    /**
     * Rename a column.
     */
    public function renameColumn(string $from, string $to, string $typeDef = 'INT(11)'): void
    {
        $pdo        = DB::getPDO();
        $fromColumn = $pdo->quoteColumn($from);
        $toColumn   = $pdo->quoteColumn($to);

        if ($this->driver === 'mysql') {
            $this->sql[] = "CHANGE {$fromColumn} {$toColumn} {$typeDef}";
        } elseif ($this->driver === 'sqlite') {
            // SQLite doesn't support RENAME COLUMN before 3.25
            $this->sql[] = "RENAME COLUMN {$fromColumn} TO {$toColumn}";
        } else {
            $this->sql[] = "RENAME COLUMN {$fromColumn} TO {$toColumn}";
        }
    }

    /**
     * Modify an existing column definition.
     */
    public function modifyColumn(string $column, string $typeDef): void
    {
        $columnName = DB::getPDO()->quoteColumn($column);
        $this->sql[] = "MODIFY {$columnName} {$typeDef}";
    }

    // ─── Index Operations ────────────────────────────────────────

    /**
     * Add an index.
     */
    public function addIndex(string $indexName, string $column): void
    {
        $this->sql[] = "ADD INDEX {$indexName}({$column})";
    }

    /**
     * Drop an index.
     */
    public function dropIndex(string $indexName): void
    {
        $this->sql[] = "DROP INDEX {$indexName}";
    }

    /**
     * Add a foreign key constraint.
     */
    public function addForeign(string $indexName, string $column, string $refTable, string $refColumn,
                               string $onDelete = 'cascade', string $onUpdate = 'cascade'): void
    {
        $pdo      = DB::getPDO();
        $refTable = $pdo->quoteTable($refTable);
        $this->sql[] = "ADD CONSTRAINT {$indexName} FOREIGN KEY ({$column}) REFERENCES {$refTable}({$refColumn})"
            . " ON DELETE " . strtoupper($onDelete)
            . " ON UPDATE " . strtoupper($onUpdate);
    }

    /**
     * Drop a foreign key constraint.
     */
    public function dropForeign(string $indexName): void
    {
        $this->sql[] = "DROP FOREIGN KEY {$indexName}";
    }

    /**
     * Add primary key.
     *
     * @param string|string[] $columns
     */
    public function addPrimaryKey($columns): void
    {
        $cols = is_array($columns) ? implode(',', $columns) : $columns;
        $this->sql[] = "ADD PRIMARY KEY ({$cols})";
    }

    /**
     * Drop primary key.
     */
    public function dropPrimaryKey(): void
    {
        $this->sql[] = 'DROP PRIMARY KEY';
    }

    // ─── Data Seeding ────────────────────────────────────────────

    /**
     * Insert a single row into the table.
     */
    public function insert(array $row): void
    {
        $this->dataSql[] = $this->buildInsert($row);
    }

    /**
     * Batch insert multiple rows.
     *
     * @param array $rows   rows to insert (each row is an assoc array)
     * @param bool  $ignore true = skip rows with duplicate primary keys
     *                      (SQLite: INSERT OR IGNORE, MySQL: INSERT IGNORE)
     */
    public function batchInsert(array $rows, bool $ignore = false): void
    {
        if (empty($rows)) {
            return;
        }
        // Use first row for keys
        $keys       = array_keys(reset($rows));
        $q          = $this->quoteChar();
        $columns    = $q . implode("{$q},{$q}", $keys) . $q;
        $valueParts = [];

        foreach ($rows as $row) {
            $values      = array_map(function ($v) {
                if ($v === null) {
                    return 'NULL';
                }
                return is_string($v) ? "'" . $this->quoteString($v) . "'" : $v;
            }, $row);
            $valueParts[] = '(' . implode(',', $values) . ')';
        }

        $keyword = '';
        if ($ignore) {
            $keyword = $this->driver === 'mysql' ? 'IGNORE' : 'OR IGNORE';
        }
        $this->dataSql[] = "INSERT {$keyword} INTO {$this->tableName} ({$columns}) VALUES " . implode(',', $valueParts) . ';';
    }

    /**
     * Upsert (INSERT ... ON DUPLICATE KEY UPDATE) for MySQL.
     */
    public function upsert(array $row, array $updateColumns = []): void
    {
        $insertSql = $this->buildInsert($row);

        if ($this->driver === 'mysql' && !empty($updateColumns)) {
            $parts = [];
            foreach ($updateColumns as $col) {
                $escaped = DB::getPDO()->quoteColumn($col);
                $parts[] = "{$escaped} = VALUES({$escaped})";
            }
            $insertSql = rtrim($insertSql, ';') . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $parts) . ';';
        }

        $this->dataSql[] = $insertSql;
    }

    // ─── Execution ───────────────────────────────────────────────

    /**
     * Generate the ALTER TABLE SQL.
     */
    public function toSql(): string
    {
        if (empty($this->sql)) {
            return '';
        }

        $parts = [];
        foreach ($this->sql as $piece) {
            if ($piece instanceof ColumnSchema) {
                $parts[] = 'ADD COLUMN ' . (string)$piece;
            } else {
                $parts[] = $piece;
            }
        }

        return "ALTER TABLE {$this->tableName}\n  " . implode(",\n  ", $parts) . ';';
    }

    /**
     * Generate all INSERT statements.
     */
    public function toDataSql(): string
    {
        return implode("\n", $this->dataSql);
    }

    /**
     * Execute all DDL and DML statements.
     */
    public function execute(): bool
    {
        // Execute DDL
        $ddl = $this->toSql();
        if ($ddl !== '') {
            $this->exec($ddl);
        }

        // Execute DML (inserts)
        foreach ($this->dataSql as $sql) {
            $this->exec($sql);
        }

        return true;
    }

    // ─── Internals ───────────────────────────────────────────────

    protected function addedColumn(string $name, string $type): ColumnSchema
    {
        $column = new ColumnSchema($name, $type, $this->driver);
        $this->sql[] = $column;
        return $column;
    }

    private function buildInsert(array $row): string
    {
        $q       = $this->quoteChar();
        $columns = $q . implode("{$q},{$q}", array_keys($row)) . $q;
        $values  = implode("','", array_map(fn($v) => $this->quoteString((string)$v), $row));
        return "INSERT INTO {$this->tableName} ({$columns}) VALUES ('{$values}');";
    }

    /**
     * Escape a string literal according to the current driver.
     *
     * - MySQL:  addslashes (backslash escapes \ ' " NUL)
     * - SQLite/PGSQL: single quote must be doubled; backslash is literal
     *   (addslashes would double backslashes and corrupt stored data).
     */
    private function quoteString(string $value): string
    {
        if ($this->driver === 'mysql') {
            return addslashes($value);
        }
        return str_replace("'", "''", $value);
    }

    /**
     * Get the proper identifier quoting character for the current driver.
     */
    private function quoteChar(): string
    {
        return $this->driver === 'mysql' ? '`' : '"';
    }

    private function exec(string $sql): bool
    {
        if ($this->verbose) {
            echo $sql . PHP_EOL;
        }
        return DB::getPDO()->rawExec($sql);
    }
}
