<?php

namespace zap\db;

use PDO;
use PDOException;
use PDOStatement;
use zap\exception\NotSupportedException;
use zap\util\Arr;
use zap\util\Random;

class ZPDO extends PDO
{
    protected $tablePrefix;

    public $driver;

    public $rowCount = 0;

    /** @var array Query log entries */
    protected $queryLog = [];

    /** @var bool Whether query logging is enabled */
    protected $logging = false;

    /** @var int Bind parameter counter (avoids collision with Random::rand) */
    protected $bindCounter = 0;

    public function __construct($config)
    {
        $this->tablePrefix = $config['prefix'] ?? '';
        $this->driver      = $config['driver'] ?? 'mysql';
        $dsn               = $config['dsn'] ?? $this->buildDSN($config);
        $username          = $config['username'] ?? $config['user'] ?? null;
        $password          = $config['password'] ?? null;
        $options           = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        if ($this->driver === 'mysql' || $this->driver === 'mariadb') {
            $dbCharset = $config['charset'] ?? 'utf8';
            $dbCollate = $config['collate'] ?? null;
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] =
                "SET NAMES '{$dbCharset}' " . ($dbCollate ? " COLLATE '{$dbCollate}'" : '');
        }

        $options += Arr::get($config, 'options', []);
        parent::__construct($dsn, $username, $password, $options);

        if ($this->driver === 'sqlite') {
            $this->sqliteCreateFunction('REGEXP', function ($pattern, $subject) {
                return preg_match("/{$pattern}/i", $subject);
            }, 2);
        }

        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS, [Statement::class]);
    }

    // ─── DSN ──────────────────────────────────────────────────

    private function buildDSN(&$config): string
    {
        $dsnElements = [];
        switch ($this->driver) {
            case 'mysql':
            case 'mariadb':
                $dsnElements = Arr::find($config, ['host', 'port', 'dbname', 'unix_socket', 'charset']);
                break;
            case 'pgsql':
                $dsnElements = Arr::find($config, ['host', 'port', 'dbname', 'user', 'password', 'sslmode']);
                unset($config['user'], $config['password']);
                break;
            case 'sqlite':
                trigger_error('Please directly set the DSN parameters', E_USER_ERROR);
                break;
            default:
                trigger_error("{$this->driver} driver not supported", E_USER_ERROR);
        }

        return $this->driver . ':' . http_build_query($dsnElements, '', ';');
    }

    // ─── Overrides (prefix-aware) ──────────────────────────────

    public function prepare($query, $options = [])
    {
        return parent::prepare($this->prepareSQL($query), $options);
    }

    public function exec($statement)
    {
        $this->logQuery($statement, []);
        $result = parent::exec($this->prepareSQL($statement));
        $this->rowCount = $result;
        return $result;
    }

    /**
     * Execute an SQL statement and return the PDOStatement.
     * FIXED: The original signature conflicted with PDO::query().
     *
     * When $params is empty, delegates to parent PDO::query() for direct execution.
     * When $params is provided, prepares and executes with parameter binding.
     *
     * @param string $query SQL query
     * @param array  $params Parameters for prepared statement
     * @param mixed  ...$fetch_mode_args PDO fetch modes (only used when no params)
     * @return PDOStatement|false
     */
    public function query($query, $params = [], ...$fetch_mode_args)
    {
        $sql = $this->prepareSQL($query);

        if (empty($params)) {
            // No params → delegate to parent PDO::query for direct execution
            if (empty($fetch_mode_args)) {
                $stm = parent::query($sql);
            } else {
                $stm = parent::query($sql, ...$fetch_mode_args);
            }
        } else {
            // With params → prepare + execute
            $stm = $this->prepare($sql);
            $stm->execute($params);
            // Apply fetch mode if specified
            if (!empty($fetch_mode_args)) {
                $stm->setFetchMode(...$fetch_mode_args);
            }
        }

        $this->rowCount = $stm->rowCount();
        $this->logQuery($sql, $params);
        return $stm;
    }

    /**
     * Execute a SELECT and return all rows as array.
     */
    public function select(string $query, array $params = [], ...$fetch_mode_args): array
    {
        $stm = $this->prepare($query);
        $stm->execute($params);
        $this->rowCount = $stm->rowCount();
        return $stm->fetchAll();
    }

    // ─── CRUD Operations ───────────────────────────────────────

    /**
     * Insert a single row.
     */
    public function insert(string $table, array $data)
    {
        $names        = [];
        $placeholders = [];
        $params       = [];

        foreach ($data as $name => $value) {
            $names[] = $this->quoteColumn($name);
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bind = $this->nextBind();
                $placeholders[] = ':' . $bind;
                $params[$bind]  = $value;
            }
        }

        $sql = 'INSERT INTO ' . $this->quoteTable($table)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';

        $this->logQuery($sql, $params);
        $stm = $this->prepare($sql);
        $stm->execute($params);
        return $this->lastInsertId();
    }

    /**
     * Batch insert multiple rows.
     *
     * @param string $table
     * @param array  $rows  Array of associative arrays
     * @return int   Number of affected rows
     */
    public function batchInsert(string $table, array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $first    = reset($rows);
        $columns  = array_keys($first);
        $names    = array_map([$this, 'quoteColumn'], $columns);
        $params   = [];
        $allPlaceholders = [];

        foreach ($rows as $rowIndex => $row) {
            $rowPlaceholders = [];
            foreach ($columns as $col) {
                $bind = ':' . $this->nextBind();
                $rowPlaceholders[] = $bind;
                $params[$bind]     = $row[$col] ?? null;
            }
            $allPlaceholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        $sql = 'INSERT INTO ' . $this->quoteTable($table)
            . ' (' . implode(', ', $names) . ') VALUES '
            . implode(', ', $allPlaceholders);

        $stm = $this->prepare($sql);
        $stm->execute($params);
        $this->rowCount = $stm->rowCount();
        return $stm->rowCount();
    }

    /**
     * Upsert (INSERT ... ON DUPLICATE KEY UPDATE / ON CONFLICT).
     */
    public function upsert($table, $data, $duplicate = null, $primaryKeys = null)
    {
        $params       = [];
        $names        = [];
        $placeholders = [];

        foreach ($data as $name => $value) {
            $col = $this->quoteColumn($name);
            $names[] = $col;
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bind = $this->nextBind();
                $placeholders[] = ':' . $bind;
                $params[$bind]  = $value;
            }
        }

        $dupSet = [];
        if ($duplicate) {
            foreach ($duplicate as $name => $value) {
                $col = $this->quoteColumn($name);
                if ($value instanceof Expr) {
                    $dupSet[] = $col . '=' . $value->raw;
                } else {
                    $bind = $this->nextBind();
                    $dupSet[] = $col . '=:' . $bind;
                    $params[$bind] = $value;
                }
            }
        }

        $sql = 'INSERT INTO ' . $this->quoteTable($table)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';

        if (!empty($duplicate) && ($this->driver === 'mysql' || $this->driver === 'mariadb')) {
            $sql .= ' ON DUPLICATE KEY UPDATE ' . implode(',', $dupSet);
        } elseif (!empty($duplicate) && $this->driver === 'pgsql') {
            if ($primaryKeys === null) {
                reset($data);
                $primaryKeys = $this->quoteColumn(key($data));
            } elseif (is_array($primaryKeys)) {
                $primaryKeys = implode(',', array_map([$this, 'quoteColumn'], $primaryKeys));
            }
            $sql .= ' ON CONFLICT (' . $primaryKeys . ') DO UPDATE SET ' . implode(',', $dupSet);
        } elseif (!empty($duplicate)) {
            throw new NotSupportedException('Upsert only supports MySQL/MariaDB and PostgreSQL');
        }

        $stm = $this->prepare($sql);
        $stm->execute($params);
        return $this->lastInsertId() ?: true;
    }

    /**
     * REPLACE INTO operation.
     */
    public function replace(string $table, array $data): int
    {
        $params       = [];
        $names        = [];
        $placeholders = [];

        foreach ($data as $name => $value) {
            $names[] = $this->quoteColumn($name);
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bind = $this->nextBind();
                $placeholders[] = ':' . $bind;
                $params[$bind]  = $value;
            }
        }

        $sql = 'REPLACE INTO ' . $this->quoteTable($table)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';

        $stm = $this->prepare($sql);
        $stm->execute($params);
        $this->rowCount = $stm->rowCount();
        return $stm->rowCount();
    }

    /**
     * Update rows.
     */
    public function update(string $table, array $data, $conditions = '', array $params = []): int
    {
        $placeholders = [];
        $inputParams  = [];

        foreach ($data as $name => $value) {
            if ($value instanceof Expr) {
                $placeholders[] = $this->quoteColumn($name) . '=' . $value->raw;
            } else {
                $placeholders[] = $this->quoteColumn($name) . '=:' . $name;
                $inputParams[$name] = $value;
            }
        }

        $sql = 'UPDATE ' . $this->quoteTable($table) . ' SET ' . implode(', ', $placeholders);

        if (($where = $this->prepareConditions($conditions, $params, $inputParams)) !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $stm = $this->prepare($sql);
        $stm->execute($inputParams);
        $this->rowCount = $stm->rowCount();
        return $stm->rowCount();
    }

    /**
     * Delete rows.
     */
    public function delete(string $table, $conditions = '', array $params = []): int
    {
        $sql         = 'DELETE FROM ' . $this->quoteTable($table);
        $inputParams = [];

        if (($where = $this->prepareConditions($conditions, $params, $inputParams)) !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $stm = $this->prepare($sql);
        $stm->execute($inputParams);
        $this->rowCount = $stm->rowCount();
        return $stm->rowCount();
    }

    /**
     * Count rows.
     */
    public function count(string $table, $conditions = '', array $params = [])
    {
        $sql         = 'SELECT COUNT(*) AS rowcount FROM ' . $this->quoteTable($table);
        $inputParams = [];

        if (($where = $this->prepareConditions($conditions, $params, $inputParams)) !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $stm = $this->prepare($sql);
        $stm->execute($inputParams);
        return $stm->fetchColumn();
    }

    /**
     * Key-value pair query.
     */
    public function keyPair(string $table, $columns, $conditions = '', array $params = []): array
    {
        if (is_array($columns)) {
            $columns = implode(',', array_map([$this, 'quoteColumn'], $columns));
        }

        $sql         = 'SELECT ' . $columns . ' FROM ' . $this->quoteTable($table);
        $inputParams = [];

        if (($where = $this->prepareConditions($conditions, $params, $inputParams)) !== '') {
            $sql .= ' WHERE ' . $where;
        }

        $stm = $this->prepare($sql);
        $stm->execute($inputParams);
        $this->rowCount = $stm->rowCount();
        return $stm->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    // ─── Convenience Methods ───────────────────────────────────

    /**
     * Fetch all rows.
     */
    public function getAll(string $statement, array $params = [], $fetchMode = null): array
    {
        $stm = $this->prepare($statement);
        $stm->execute($params);
        return $stm->fetchAll($fetchMode);
    }

    /**
     * Fetch a single row.
     */
    public function get(string $statement, array $params = [], $fetchMode = null)
    {
        $stm = $this->prepare($statement);
        $stm->execute($params);
        return $stm->fetch($fetchMode);
    }

    /**
     * Fetch a single column value.
     */
    public function value(string $statement, array $params = [])
    {
        $stm = $this->prepare($statement);
        $stm->execute($params);
        return $stm->fetchColumn();
    }

    // ─── Query Builder Factory ─────────────────────────────────

    public function table(string $table, ?string $alias = null): Query
    {
        $query = new Query($this);
        return $query->from($table, $alias ?? '');
    }

    // ─── Schema / DDL ──────────────────────────────────────────

    public function rawExec($statement)
    {
        return parent::exec($statement);
    }

    public function renameTable($oldName, $newName)
    {
        return $this->exec('RENAME TABLE ' . $this->quoteTable($oldName) . ' TO ' . $this->quoteTable($newName));
    }

    public function dropTable($table)
    {
        return $this->exec('DROP TABLE ' . $this->quoteTable($table));
    }

    public function truncateTable($table)
    {
        return $this->exec('TRUNCATE TABLE ' . $this->quoteTable($table));
    }

    public function getTables(): array
    {
        if ($this->driver === 'sqlite') {
            return $this->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
                ->fetchAll(PDO::FETCH_COLUMN);
        }
        if ($this->driver === 'mysql' || $this->driver === 'mariadb') {
            return $this->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        }
        return [];
    }

    public function getTableStructure(string $table): string
    {
        $tbl = $this->quoteTable($table);
        if ($this->driver === 'sqlite') {
            return (string) $this->query(
                "SELECT sql FROM sqlite_master WHERE name=?",
                [$this->unPrefixTable($table)]
            )->fetchColumn();
        }
        if ($this->driver === 'mysql' || $this->driver === 'mariadb') {
            return (string) $this->query("SHOW CREATE TABLE {$tbl}")->fetchColumn(1);
        }
        return '';
    }

    public function getTableColumns(string $table): array
    {
        $tbl = $this->quoteTable($table);
        if ($this->driver === 'sqlite') {
            return $this->query("PRAGMA table_info({$tbl})")->fetchAll();
        }
        if ($this->driver === 'mysql' || $this->driver === 'mariadb') {
            return $this->query("SHOW COLUMNS FROM {$tbl}")->fetchAll();
        }
        return [];
    }

    // ─── Transaction Helpers ───────────────────────────────────

    /**
     * Begin a transaction on this connection.
     */
    public function beginTrans(): bool
    {
        return $this->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    public function commitTrans(): bool
    {
        return $this->commit();
    }

    /**
     * Rollback the current transaction.
     */
    public function rollbackTrans(): bool
    {
        return $this->rollBack();
    }

    /**
     * Check if currently in a transaction.
     */
    public function inTransaction(): bool
    {
        return parent::inTransaction();
    }

    // ─── Query Logging ─────────────────────────────────────────

    public function enableQueryLog(): void
    {
        $this->logging = true;
    }

    public function disableQueryLog(): void
    {
        $this->logging = false;
    }

    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    public function flushQueryLog(): void
    {
        $this->queryLog = [];
    }

    protected function logQuery(string $sql, array $params): void
    {
        if ($this->logging) {
            $this->queryLog[] = [
                'sql'    => $sql,
                'params' => $params,
                'time'   => microtime(true),
            ];
        }
    }

    // ─── Quote / Prefix ────────────────────────────────────────

    public function setTablePrefix($prefix): void
    {
        $this->tablePrefix = $prefix;
    }

    public function unPrefixTable($table): string
    {
        if ($this->tablePrefix && str_starts_with($table, $this->tablePrefix)) {
            return substr($table, strlen($this->tablePrefix));
        }
        return $table;
    }

    public function prepareSQL($sql): string
    {
        if ($this->tablePrefix) {
            return preg_replace_callback('/\{([\w\-\. ]+?)\}/', function ($matches) {
                return $this->quoteTable($matches[1]);
            }, $sql);
        }
        return $sql;
    }

    public function quoteColumn($columnName): string
    {
        $colAlias = explode('.', $columnName);
        if (count($colAlias) === 2) {
            return $this->quoteColumn($colAlias[0]) . '.' . $this->quoteColumn($colAlias[1]);
        }

        switch ($this->driver) {
            case 'mysql':
            case 'mariadb':
                return "`{$columnName}`";
            case 'mssql':
                return "[{$columnName}]";
            case 'pgsql':
                return '"' . $columnName . '"';
            default:
                return $columnName;
        }
    }

    public function quoteTable($table): string
    {
        $table = $this->tablePrefix . $table;

        switch ($this->driver) {
            case 'mysql':
            case 'mariadb':
                return '`' . $table . '`';
            case 'mssql':
                return "[{$table}]";
            case 'pgsql':
                return '"' . $table . '"';
            default:
                return $table;
        }
    }

    public function quote($value, $type = PDO::PARAM_STR)
    {
        if (is_array($value)) {
            return array_map(fn($v) => $this->quote($v), $value);
        }
        return parent::quote((string) $value, $type);
    }

    // ─── Info ──────────────────────────────────────────────────

    public function info(): array
    {
        $keyNames = [
            'server'     => PDO::ATTR_SERVER_INFO,
            'driver'     => PDO::ATTR_DRIVER_NAME,
            'client'     => PDO::ATTR_CLIENT_VERSION,
            'version'    => PDO::ATTR_SERVER_VERSION,
            'connection' => PDO::ATTR_CONNECTION_STATUS,
        ];

        foreach ($keyNames as $key => $value) {
            try {
                $keyNames[$key] = $this->getAttribute($value);
            } catch (PDOException $e) {
                $keyNames[$key] = $e->getMessage();
            }
        }

        return $keyNames;
    }

    public function setFetchMode($mode): bool
    {
        return $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $mode);
    }

    public function setAutoCommit($value): bool
    {
        return $this->setAttribute(PDO::ATTR_AUTOCOMMIT, $value);
    }

    public function getAutoCommit()
    {
        return $this->getAttribute(PDO::ATTR_AUTOCOMMIT);
    }

    public function rowCount(): int
    {
        return $this->rowCount;
    }

    // ─── Helpers ───────────────────────────────────────────────

    public function buildParams($array, $name): array
    {
        $params = [];
        $values = [];
        $parts  = explode('.', $name);
        $name   = $parts[1] ?? $parts[0];

        for ($i = 0; $i < count($array); $i++) {
            $params[$i]         = ":{$name}{$i}";
            $values[$params[$i]] = $array[$i];
        }

        return [implode(',', $params), $values];
    }

    public function statement($statement, array $params = [])
    {
        $stm = $this->prepare($statement);
        $stm->execute($params);
        $this->rowCount = $stm->rowCount();
        return $stm;
    }

    public function toSnakeCase($name): string
    {
        $name = preg_replace('/([A-Z])/', '_$1', $name);
        return strtolower(trim($name, '_'));
    }

    public function lastId()
    {
        return $this->lastInsertId();
    }

    // ─── Internals ─────────────────────────────────────────────

    private function prepareConditions($conditions, $params, &$inputParams): string
    {
        if (is_array($conditions)) {
            $lines = [];
            $i     = 0;
            foreach ($conditions as $name => $value) {
                if ($value instanceof Expr) {
                    $lines[] = $this->quoteColumn($name) . '=' . $value->raw;
                } else {
                    $bindName  = $this->nextBind() . $i;
                    $lines[]  = $this->quoteColumn($name) . '=:' . $bindName;
                    $inputParams[$bindName] = $value;
                }
                $i++;
            }
            return implode(' AND ', $lines);
        }

        if (is_string($conditions) && is_array($params) && Arr::isAssoc($params)) {
            foreach ($params as $name => $value) {
                if (!($value instanceof Expr)) {
                    $inputParams[$name] = $value;
                }
                // Expr values are already embedded in the conditions string
            }
            return $conditions;
        }

        if (is_string($conditions) && (is_scalar($params) || (is_array($params) && !Arr::isAssoc($params)))) {
            if (is_scalar($params)) {
                $params = [$params];
            }
            $inputParams += $params;
            return $conditions;
        }

        return '';
    }

    /**
     * Generate a unique bind parameter name for this instance.
     */
    protected function nextBind(): string
    {
        return '_b' . (++$this->bindCounter);
    }
}
