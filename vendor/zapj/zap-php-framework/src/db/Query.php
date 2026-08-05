<?php

namespace zap\db;

use zap\util\Arr;
use zap\util\Pagination;

class Query
{
    // Fetch mode constants
    const FETCH_ASSOC  = 2;
    const FETCH_OBJ    = 5;
    const FETCH_COLUMN = 7;
    const FETCH_KEY_PAIR = 12;

    /**
     * Distinct select.
     */
    protected $distinct = false;

    /**
     * The columns to be returned.
     */
    protected $columns = [];

    /**
     * The table the query is targeting.
     */
    protected $from = '';

    /**
     * WHERE constraints.
     */
    protected $wheres = [];

    /**
     * HAVING constraints.
     */
    protected $havings = [];

    /**
     * ORDER BY clauses.
     */
    protected $orders = [];

    /**
     * GROUP BY clauses.
     */
    protected $groups = [];

    /**
     * JOIN clauses.
     */
    protected $joins = [];

    /**
     * The query UNION statements.
     */
    protected $unions = [];

    /**
     * Table alias.
     */
    protected $alias = '';

    /**
     * LIMIT value.
     */
    protected $limit = null;

    /**
     * OFFSET value.
     */
    protected $offset = null;

    /**
     * Bind parameters.
     */
    protected $params = [];

    /**
     * SET fields for UPDATE.
     */
    protected $fields = [];

    /**
     * Database connection instance.
     *
     * @var ZPDO
     */
    protected $db;

    /**
     * Select type (SELECT, INSERT, UPDATE, DELETE).
     */
    protected $type = 'select';

    /** @var array Cached where bind values for logging */
    protected $bindings = [];

    public function __construct($db = null, string $from = '', string $alias = '')
    {
        if ($db instanceof ZPDO) {
            $this->db = $db;
        }
        $this->from = $from;
        if ($alias) {
            $this->alias = $alias;
        }
    }

    /**
     * Create a new query instance.
     */
    public static function query($db = null, string $from = '', string $alias = ''): self
    {
        return new self($db, $from, $alias);
    }

    // ─── Select / From ────────────────────────────────────────

    public function select(...$columns): self
    {
        $this->type    = 'select';
        $this->columns = $columns;

        return $this;
    }

    public function addSelect(...$columns): self
    {
        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    public function distinct(): self
    {
        $this->distinct = true;

        return $this;
    }

    public function from(string $table, string $alias = ''): self
    {
        $this->from = $table;
        if ($alias) {
            $this->alias = $alias;
        }

        return $this;
    }

    // ─── Where Clauses ─────────────────────────────────────────

    /**
     * Add a basic WHERE clause.
     *
     * @param string|array|\Closure $column
     * @param string|null $operator
     * @param mixed $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null): self
    {
        // Allow passing a where array directly
        if (is_array($column)) {
            $this->_where($column);
            return $this;
        }

        if ($column instanceof \Closure) {
            $query = new self($this->db, $this->from, $this->alias);
            $column($query);
            $this->wheres[] = ['type' => 'nested', 'query' => $query];
            return $this;
        }

        if (func_num_args() === 2) {
            [$value, $operator] = [$operator, '='];
        }

        return $this->_where([$column => [
            'operator' => $operator ?? '=',
            'value'    => $value,
            'boolean'  => 'AND',
        ]]);
    }

    public function orWhere($column, $operator = null, $value = null): self
    {
        // Allow passing a where array directly
        if (is_array($column)) {
            foreach ($column as $key => $val) {
                if (is_numeric($key)) {
                    $this->_where([$val['column'] => [
                        'operator' => $val['operator'] ?? '=',
                        'value'    => $val['value'] ?? null,
                        'boolean'  => 'OR',
                    ]]);
                } else {
                    $this->_where([$key => [
                        'operator' => '=',
                        'value'    => $val,
                        'boolean'  => 'OR',
                    ]]);
                }
            }
            return $this;
        }

        if ($column instanceof \Closure) {
            $query = new self($this->db, $this->from, $this->alias);
            $column($query);
            $this->wheres[] = ['type' => 'nested', 'query' => $query, 'boolean' => 'OR'];
            return $this;
        }

        if (func_num_args() === 2) {
            [$value, $operator] = [$operator, '='];
        }

        return $this->_where([$column => [
            'operator' => $operator ?? '=',
            'value'    => $value,
            'boolean'  => 'OR',
        ]]);
    }

    public function whereBetween(string $column, $values, string $boolean = 'AND', bool $not = false): self
    {
        $operator = $not ? 'NOT BETWEEN' : 'BETWEEN';

        return $this->_where([$column => [
            'operator' => $operator,
            'value'    => (array) $values,
            'boolean'  => $boolean,
        ]]);
    }

    public function whereNotBetween(string $column, $values, string $boolean = 'AND'): self
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    public function whereIn(string $column, $values, string $boolean = 'AND', bool $not = false): self
    {
        $operator = $not ? 'NOT IN' : 'IN';

        return $this->_where([$column => [
            'operator' => $operator,
            'value'    => (array) $values,
            'boolean'  => $boolean,
        ]]);
    }

    public function whereNotIn(string $column, $values, string $boolean = 'AND'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    public function whereNull(string $column, string $boolean = 'AND', bool $not = false): self
    {
        $operator = $not ? 'IS NOT NULL' : 'IS NULL';

        return $this->_where([$column => [
            'operator' => $operator,
            'value'    => null,
            'boolean'  => $boolean,
        ]]);
    }

    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        return $this->whereNull($column, $boolean, true);
    }

    public function orWhereNull(string $column): self
    {
        return $this->whereNull($column, 'OR');
    }

    public function orWhereNotNull(string $column): self
    {
        return $this->whereNull($column, 'OR', true);
    }

    /**
     * Compare two columns.
     */
    public function whereColumn(string $first, string $operator, string $second = null, string $boolean = 'AND'): self
    {
        if ($second === null) {
            [$second, $operator] = [$operator, '='];
        }

        $col1 = $this->db ? $this->db->quoteColumn($first) : $first;
        $col2 = $this->db ? $this->db->quoteColumn($second) : $second;

        return $this->_where([$col1 => [
            'operator' => $operator,
            'value'    => new Expr($col2),
            'boolean'  => $boolean,
        ]]);
    }

    public function orWhereColumn(string $first, string $operator, string $second = null): self
    {
        return $this->whereColumn($first, $operator, $second, 'OR');
    }

    /**
     * Internal where builder.
     * Accepts: [colName => [operator, value, boolean]]
     *
     * @param array $conditions
     * @return $this
     */
    protected function _where(array $conditions): self
    {
        foreach ($conditions as $colName => $condition) {
            if (!is_array($condition)) {
                // Simple [col => value] format
                $condition = [
                    'operator' => '=',
                    'value'    => $condition,
                    'boolean'  => 'AND',
                ];
            }

            $operator = strtoupper($condition['operator'] ?? '=');
            $value    = $condition['value'] ?? null;
            $boolean  = $condition['boolean'] ?? 'AND';

            if ($this->db) {
                $colName = $this->db->quoteColumn($colName);
            }

            switch ($operator) {
                case 'IN':
                case 'NOT IN':
                    $this->prepareWhereInStatement($colName, $operator, (array) $value, $boolean);
                    break;

                case 'BETWEEN':
                case 'NOT BETWEEN':
                    // Parameterized BETWEEN to prevent SQL injection
                    $paramCount = count($this->params);
                    $b1 = '_bet1_' . $paramCount;
                    $b2 = '_bet2_' . $paramCount;
                    $values    = (array) $value;
                    $this->wheres[] = [
                        'sql'     => "{$colName} {$operator} :{$b1} AND :{$b2}",
                        'boolean' => $boolean,
                    ];
                    $this->params[$b1] = $values[0] ?? null;
                    $this->params[$b2] = $values[1] ?? null;
                    break;

                case 'IS NULL':
                case 'IS NOT NULL':
                    $this->wheres[] = [
                        'sql'     => "{$colName} {$operator}",
                        'boolean' => $boolean,
                    ];
                    break;

                default:
                    if ($value instanceof Expr) {
                        $this->wheres[] = [
                            'sql'     => "{$colName} {$operator} {$value->raw}",
                            'boolean' => $boolean,
                        ];
                    } elseif ($value instanceof \Closure) {
                        $sub = new self($this->db, $this->from, $this->alias);
                        $value($sub);
                        $this->wheres[] = [
                            'type'    => 'nested',
                            'query'   => $sub,
                            'boolean' => $boolean,
                        ];
                    } else {
                        $bind = '_w' . count($this->params);
                        $this->wheres[] = [
                            'sql'     => "{$colName} {$operator} :{$bind}",
                            'boolean' => $boolean,
                        ];
                        $this->params[$bind] = $value;
                    }
                    break;
            }
        }

        return $this;
    }

    // ─── Having ────────────────────────────────────────────────

    public function having(string $column, $operator = null, $value = null, string $boolean = 'AND'): self
    {
        if (func_num_args() === 2) {
            [$value, $operator] = [$operator, '='];
        }

        $this->havings[] = [
            'column'   => $column,
            'operator' => $operator ?? '=',
            'value'    => $value,
            'boolean'  => $boolean,
        ];

        return $this;
    }

    public function orHaving(string $column, $operator = null, $value = null): self
    {
        return $this->having($column, $operator, $value, 'OR');
    }

    // ─── Group By / Order By ───────────────────────────────────

    public function groupBy(...$columns): self
    {
        if (count($columns) === 1 && is_array($columns[0])) {
            $columns = $columns[0];
        }
        $this->groups = array_merge($this->groups, $columns);
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        // Support comma-separated multiple order clauses like "col1 ASC,col2 DESC"
        if (strpos($column, ',') !== false) {
            foreach (explode(',', $column) as $clause) {
                $clause = trim($clause);
                if ($clause !== '') {
                    // Split "col1 ASC" into column and direction
                    $parts = preg_split('/\s+/', $clause, 2);
                    $col = $parts[0];
                    $dir = $parts[1] ?? $direction;
                    $this->orderBy($col, $dir);
                }
            }
            return $this;
        }

        // Detect if column already includes ASC/DESC suffix
        $columnTrim = trim($column);
        if (preg_match('/\s+(ASC|DESC)$/i', $columnTrim, $matches)) {
            $column = trim(substr($columnTrim, 0, -(strlen($matches[0]))));
            $direction = strtoupper($matches[1]);
        } else {
            $direction = strtoupper($direction);
            $direction = in_array($direction, ['ASC', 'DESC']) ? $direction : 'ASC';
        }

        if ($this->db) {
            $column = $this->db->quoteColumn($column);
        }
        $this->orders[] = "{$column} {$direction}";
        return $this;
    }

    public function orderByDesc(string $column): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function latest(string $column = 'id'): self
    {
        return $this->orderBy($column, 'DESC');
    }

    public function oldest(string $column = 'id'): self
    {
        return $this->orderBy($column, 'ASC');
    }

    public function inRandomOrder(): self
    {
        if ($this->db && ($this->db->driver === 'mysql' || $this->db->driver === 'mariadb')) {
            $this->orders[] = 'RAND()';
        } else {
            $this->orders[] = 'RANDOM()';
        }
        return $this;
    }

    public function reorder(string $column = 'id', string $direction = 'ASC'): self
    {
        $this->orders = [];
        return $this->orderBy($column, $direction);
    }

    // ─── Join ──────────────────────────────────────────────────

    public function join($table, $alias = '', $on = '', string $type = 'INNER'): self
    {
        // Support old API: join(['table', 'alias'], $on)
        // In old API, second argument is the ON condition (not an alias)
        if (is_array($table)) {
            $on    = $alias !== '' ? (string)$alias : $on;
            $alias = $table[1] ?? '';
            $table = $table[0];
        }

        if ($this->db) {
            $table = $this->db->quoteTable($table);
        }

        $clause = "{$type} JOIN {$table}";
        if ($alias) {
            $clause .= " AS {$alias}";
        }
        if ($on) {
            $clause .= " ON {$on}";
        }

        $this->joins[] = $clause;
        return $this;
    }

    public function leftJoin($table, $alias = '', $on = ''): self
    {
        return $this->join($table, $alias, $on, 'LEFT');
    }

    public function rightJoin($table, $alias = '', $on = ''): self
    {
        return $this->join($table, $alias, $on, 'RIGHT');
    }

    public function crossJoin(string $table, string $alias = ''): self
    {
        return $this->join($table, $alias, '', 'CROSS');
    }

    // ─── Union ─────────────────────────────────────────────────

    public function union(Query $query, bool $all = false): self
    {
        $this->unions[] = ['query' => $query, 'all' => $all];
        return $this;
    }

    public function unionAll(Query $query): self
    {
        return $this->union($query, true);
    }

    // ─── Limit / Offset / Paginate ─────────────────────────────

    public function limit($value): self
    {
        $this->limit = (int) $value;
        return $this;
    }

    public function offset($value): self
    {
        $this->offset = max(0, (int) $value);
        return $this;
    }

    public function take(int $value): self
    {
        return $this->limit($value);
    }

    public function skip(int $value): self
    {
        return $this->offset($value);
    }

    /**
     * Execute a paginated query and return a Pagination instance.
     */
    public function paginate(int $perPage = 15, ?int $page = null): Pagination
    {
        $page = $page ?: (int) ($_GET['page'] ?? 1);
        $page = max(1, $page);

        $total = $this->count();
        $pagination = Pagination::make($total, $perPage, $page);

        $offset = $pagination->offset();
        $this->limit($perPage)->offset($offset);

        $items = $this->get();
        $pagination->setTotal($total);

        return $pagination;
    }

    // ─── Insert / Update / Delete ──────────────────────────────

    /**
     * Insert a new record.
     */
    public function insert(array $data)
    {
        if ($this->db) {
            return $this->db->insert($this->from, $data);
        }

        $names        = [];
        $placeholders = [];
        $params       = [];

        foreach ($data as $name => $value) {
            $names[] = $name;
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bind = ':i' . count($params);
                $placeholders[] = $bind;
                $params[$bind] = $value;
            }
        }

        $sql = 'INSERT INTO ' . $this->from
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';

        $this->type = 'insert';

        $stm = $this->db->prepare($sql);
        $stm->execute($params);
        return $this->db->lastInsertId();
    }

    /**
     * Set fields for UPDATE query.
     * Supports: set($key, $value) or set(['key' => 'value', ...])
     */
    public function set($params, $value = null): self
    {
        $this->type = 'update';

        if ($value !== null) {
            $params = [$params => $value];
        }

        if ($this->db) {
            foreach ((array)$params as $name => $param) {
                if ($param instanceof Expr) {
                    $this->fields[] = $this->db->quoteColumn($name) . '=' . $param->raw;
                } else {
                    $this->params[$name] = $param;
                    $this->fields[] = $this->db->quoteColumn($name) . '=:' . $name;
                }
            }
        }

        return $this;
    }

    /**
     * Delete records.
     */
    public function delete($id = null): int
    {
        $this->type = 'delete';

        if ($id !== null) {
            $this->where('id', '=', $id);
        }

        $deleteSQL = $this->getSQL();
        $stm = $this->db->prepare($deleteSQL);
        $stm->execute($this->params);

        return $stm->rowCount();
    }

    public function insertGetId(array $data)
    {
        return $this->insert($data);
    }

    public function insertOrIgnore(array $data): bool
    {
        $names        = [];
        $placeholders = [];
        $params       = [];

        foreach ($data as $name => $value) {
            $names[] = $this->db ? $this->db->quoteColumn($name) : $name;
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bind = '_ii' . count($params);
                $placeholders[] = ':' . $bind;
                $params[$bind] = $value;
            }
        }

        $prefix = ($this->db && ($this->db->driver === 'mysql' || $this->db->driver === 'mariadb'))
            ? 'INSERT IGNORE INTO ' : 'INSERT OR IGNORE INTO ';

        $sql = $prefix . ($this->db ? $this->db->quoteTable($this->from) : $this->from)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';

        $stm = $this->db->prepare($sql);
        return $stm->execute($params);
    }

    // ─── Execution ─────────────────────────────────────────────

    public function get(int $fetchMode = self::FETCH_ASSOC): array
    {
        $sql = $this->getSQL();
        $stm = $this->db->prepare($sql);
        $stm->execute($this->params);
        return $stm->fetchAll($fetchMode);
    }

    /**
     * Fetch all results with the given fetch mode.
     */
    public function fetchAll(int $fetchMode = self::FETCH_ASSOC): array
    {
        return $this->get($fetchMode);
    }

    /**
     * Fetch a single record.
     */
    public function fetch(int $fetchMode = self::FETCH_ASSOC)
    {
        $this->limit(1);
        $results = $this->get($fetchMode);
        return $results[0] ?? null;
    }

    public function first()
    {
        $result = $this->limit(1)->get();
        return $result[0] ?? null;
    }

    public function find($id)
    {
        return $this->where('id', '=', $id)->first();
    }

    /**
     * Get a single column's value from the first result.
     */
    public function value(string $column)
    {
        $result = $this->first();
        if ($result) {
            return $result->{$column} ?? $result[$column] ?? null;
        }
        return null;
    }

    /**
     * Get an array of values for a single column.
     */
    public function pluck(string $column, string $key = null): array
    {
        $this->columns = $key ? [$key, $column] : [$column];
        $results = $this->get();

        if ($key) {
            $plucked = [];
            foreach ($results as $result) {
                $keyVal = is_object($result) ? $result->{$key} : $result[$key];
                $plucked[$keyVal] = is_object($result) ? $result->{$column} : $result[$column];
            }
            return $plucked;
        }

        return array_map(function ($result) use ($column) {
            return is_object($result) ? $result->{$column} : $result[$column];
        }, $results);
    }

    public function count(string $column = '*'): int
    {
        $clone          = clone $this;
        $clone->columns = [];
        $clone->orders  = [];
        $clone->limit   = null;
        $clone->offset  = null;

        $clone->type = 'select';
        $sql = $clone->getSQL();

        // Build COUNT SQL manually
        $select = 'SELECT COUNT(' . ($column !== '*' ? $column : '*') . ') AS _count';
        $fromPos = stripos($sql, 'FROM');
        $countSQL = $fromPos !== false ? $select . ' ' . substr($sql, $fromPos) : $select . ' ' . $sql;

        // Remove ORDER BY for efficiency
        $orderPos = stripos($countSQL, 'ORDER BY');
        if ($orderPos !== false) {
            $countSQL = substr($countSQL, 0, $orderPos);
        }

        // Remove UNION sections for count (only count first query)
        $unionPos = stripos($countSQL, 'UNION');
        if ($unionPos !== false) {
            $countSQL = substr($countSQL, 0, $unionPos);
        }

        $stm = $this->db->prepare($countSQL);
        $stm->execute($clone->params);
        return (int) $stm->fetchColumn();
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    public function doesntExist(): bool
    {
        return !$this->exists();
    }

    public function increment(string $column, int $amount = 1, array $extra = []): int
    {
        $extra[$column] = new Expr($column . ' + ' . $amount);
        $this->type = 'update';
        return $this->updateQuery($extra);
    }

    public function decrement(string $column, int $amount = 1, array $extra = []): int
    {
        $extra[$column] = new Expr($column . ' - ' . $amount);
        $this->type = 'update';
        return $this->updateQuery($extra);
    }

    /**
     * Execute an UPDATE query. Supports two patterns:
     *  - Chained:   ->set('col', val)->set('col2', val2)->where(...)->update()
     *  - Direct:    ->where(...)->update(['col' => val])
     */
    public function update(array $data = null): int
    {
        $this->type = 'update';

        if ($data !== null) {
            return $this->updateQuery($data);
        }

        if (empty($this->fields)) {
            return 0;
        }

        $sql = 'UPDATE ' . ($this->db ? $this->db->quoteTable($this->from) : $this->from);
        $sql .= ' SET ' . implode(', ', $this->fields);
        $sql .= $this->prepareWhereString();
        $sql .= $this->prepareLimitString();
        $sql .= $this->prepareOrderString();

        $stm = $this->db->prepare($sql);
        $stm->execute($this->params);
        return $stm->rowCount();
    }

    /**
     * Execute an update query via the Query builder.
     */
    protected function updateQuery(array $data): int
    {
        $setClauses = [];
        foreach ($data as $name => $value) {
            if ($value instanceof Expr) {
                $setClauses[] = ($this->db ? $this->db->quoteColumn($name) : $name) . '=' . $value->raw;
            } else {
                $bind = '_upd' . count($this->params);
                $setClauses[] = ($this->db ? $this->db->quoteColumn($name) : $name) . '=:' . $bind;
                $this->params[$bind] = $value;
            }
        }

        $sql = 'UPDATE ' . ($this->db ? $this->db->quoteTable($this->from) : $this->from)
            . ' SET ' . implode(', ', $setClauses);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->prepareWheres();
        }

        $stm = $this->db->prepare($sql);
        $stm->execute($this->params);
        return $stm->rowCount();
    }

    /**
     * Aggregate functions.
     */
    public function max(string $column)
    {
        return $this->aggregate('MAX', $column);
    }

    public function min(string $column)
    {
        return $this->aggregate('MIN', $column);
    }

    public function sum(string $column)
    {
        return $this->aggregate('SUM', $column);
    }

    public function avg(string $column)
    {
        return $this->aggregate('AVG', $column);
    }

    protected function aggregate(string $function, string $column)
    {
        $clone          = clone $this;
        $clone->columns = [];
        $clone->orders  = [];
        $clone->limit   = null;
        $clone->offset  = null;

        $clone->type = 'select';
        $sql = $clone->getSQL();

        $selectExpr = "SELECT {$function}({$column}) AS _aggregate";
        $fromPos = stripos($sql, 'FROM');
        $aggSQL = $fromPos !== false ? $selectExpr . ' ' . substr($sql, $fromPos) : $selectExpr . ' ' . $sql;

        $stm = $this->db->prepare($aggSQL);
        $stm->execute($clone->params);
        return $stm->fetchColumn();
    }

    // ─── Chunking ──────────────────────────────────────────────

    /**
     * Process results in chunks to avoid memory issues.
     */
    public function chunk(int $size, callable $callback): bool
    {
        $page = 1;

        do {
            $clone  = clone $this;
            $clone->limit($size)->offset(($page - 1) * $size);
            $results = $clone->get();

            $count = count($results);

            if ($count === 0) {
                break;
            }

            if ($callback($results, $page) === false) {
                return false;
            }

            $page++;
        } while ($count === $size);

        return true;
    }

    /**
     * Process results one by one.
     */
    public function each(callable $callback): bool
    {
        return $this->chunk(100, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if ($callback($value, $key) === false) {
                    return false;
                }
            }
            return true;
        });
    }

    /**
     * Execute the query and get an array result.
     */
    public function toArray(): array
    {
        return $this->get();
    }

    /**
     * Execute the query and get JSON result.
     */
    public function toJson(int $options = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->get(), $options);
    }

    // ─── SQL Building ──────────────────────────────────────────

    public function getSQL(): string
    {
        $query = '';
        switch ($this->type) {
            case 'select':
                $query = $this->prepareSelectString() . $this->prepareFrom()
                    . $this->prepareJoinString()
                    . $this->prepareWhereString()
                    . $this->prepareGroupByString()
                    . $this->prepareOrderString()
                    . $this->prepareLimitString()
                    . $this->prepareUnionString();
                break;

            case 'update':
                $query = 'UPDATE ' . $this->prepareFrom();
                if (!empty($this->fields)) {
                    $query .= ' SET ' . implode(', ', $this->fields);
                }
                $query .= $this->prepareWhereString()
                    . $this->prepareOrderString()
                    . $this->prepareLimitString();
                break;

            case 'delete':
                $query = 'DELETE FROM ' . $this->prepareFrom();
                if ($this->alias) {
                    $query .= ' AS ' . $this->alias;
                }
                $query .= $this->prepareWhereString()
                    . $this->prepareOrderString()
                    . $this->prepareLimitString();
                break;

            case 'insert':
                // Handled separately in insert() method
                break;
        }

        return $query;
    }

    /**
     * Get raw SQL with actual values substituted (for debugging only, not safe).
     */
    public function toSql(): string
    {
        $sql = $this->getSQL();

        foreach ($this->params as $key => $value) {
            $sql = str_replace(
                ':' . $key,
                is_numeric($value) ? $value : "'" . addslashes((string) $value) . "'",
                $sql
            );
        }

        return $sql;
    }

    /**
     * Get all current bindings.
     */
    public function getBindings(): array
    {
        return $this->params;
    }

    /**
     * Dump SQL with bindings for debugging.
     */
    public function dd(): void
    {
        echo $this->toSql() . PHP_EOL;
        die;
    }

    /**
     * Dump SQL without dying.
     */
    public function dump(): self
    {
        echo $this->toSql() . PHP_EOL;
        return $this;
    }

    // ─── Prepare Query Components ──────────────────────────────

    protected function prepareFrom(): string
    {
        $table = $this->db ? $this->db->quoteTable($this->from) : $this->from;
        if ($this->alias) {
            $table .= ' AS ' . $this->alias;
        }
        return $table;
    }

    protected function prepareSelectString(): string
    {
        $select = $this->distinct ? 'SELECT DISTINCT ' : 'SELECT ';
        if (count($this->columns) > 0) {
            $columns = [];
            foreach ($this->columns as $column) {
                if ($column instanceof Expr) {
                    $columns[] = $column->raw;
                } elseif ($this->db) {
                    $columns[] = $this->db->quoteColumn($column);
                } else {
                    $columns[] = $column;
                }
            }
            $select .= implode(', ', $columns);
        } else {
            $select .= '*';
        }
        return $select . ' FROM ';
    }

    protected function prepareUnionString(): string
    {
        if (empty($this->unions)) {
            return '';
        }

        $sql = '';
        foreach ($this->unions as $union) {
            $sql .= ($union['all'] ? ' UNION ALL ' : ' UNION ')
                . '(' . $union['query']->getSQL() . ')';
            $this->params = array_merge($this->params, $union['query']->getBindings());
        }
        return $sql;
    }

    protected function prepareJoinString(): string
    {
        return $this->joins ? ' ' . implode(' ', $this->joins) : '';
    }

    protected function prepareWhereString(): string
    {
        $wheres = $this->prepareWheres();
        return $wheres ? ' WHERE ' . $wheres : '';
    }

    protected function prepareWheres(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $whereStatements = [];
        foreach ($this->wheres as $where) {
            $bool = strtoupper($where['boolean'] ?? 'AND');

            if (isset($where['type']) && $where['type'] === 'nested') {
                $nestedWheres = $where['query']->prepareWheres();
                if ($nestedWheres !== '') {
                    $prefix = empty($whereStatements) ? '' : " {$bool} ";
                    $whereStatements[] = $prefix . '(' . $nestedWheres . ')';
                    $this->params = array_merge($this->params, $where['query']->getBindings());
                }
            } elseif (isset($where['sql'])) {
                $prefix = empty($whereStatements) ? '' : " {$bool} ";
                $whereStatements[] = $prefix . $where['sql'];
            }
        }

        return implode('', $whereStatements);
    }

    protected function prepareGroupByString(): string
    {
        if (empty($this->groups)) {
            return '';
        }
        return ' GROUP BY ' . implode(', ', $this->groups);
    }

    protected function prepareHavingString(): string
    {
        if (empty($this->havings)) {
            return '';
        }

        $clauses = [];
        foreach ($this->havings as $i => $having) {
            $bool = strtoupper($having['boolean'] ?? 'AND');
            $prefix = ($i === 0) ? '' : " {$bool} ";
            $clauses[] = $prefix . $having['column'] . ' ' . $having['operator'] . ' ?';
            // For having, we add params as positional
            $this->params['_hav' . $i] = $having['value'];
            $clauses[count($clauses) - 1] = $prefix . $having['column'] . ' ' . $having['operator'] . ' :_hav' . $i;
        }

        return ' HAVING ' . implode('', $clauses);
    }

    protected function prepareOrderString(): string
    {
        return $this->orders ? ' ORDER BY ' . implode(', ', $this->orders) : '';
    }

    protected function prepareLimitString(): string
    {
        // Use is_null to properly handle limit(0)
        if ($this->limit === null) {
            return '';
        }
        $sql = ' LIMIT ' . (int) $this->limit;
        if ($this->offset !== null && $this->offset > 0) {
            $sql .= ' OFFSET ' . (int) $this->offset;
        }
        return $sql;
    }

    protected function prepareWhereInStatement(string $colName, string $operator, array $values, string $boolean): void
    {
        if (empty($values)) {
            // Empty IN () → always false
            $this->wheres[] = [
                'sql'     => $operator === 'NOT IN' ? '1=1' : '1=0',
                'boolean' => $boolean,
            ];
            return;
        }

        $placeholders = [];
        foreach ($values as $val) {
            $bind = '_in' . count($this->params);
            $placeholders[] = ':' . $bind;
            $this->params[$bind] = $val;
        }

        $this->wheres[] = [
            'sql'     => "{$colName} {$operator} (" . implode(', ', $placeholders) . ')',
            'boolean' => $boolean,
        ];
    }
}

// Global fetch mode constants for backward compatibility
defined('FETCH_ASSOC')   || define('FETCH_ASSOC',   Query::FETCH_ASSOC);
defined('FETCH_OBJ')     || define('FETCH_OBJ',     Query::FETCH_OBJ);
defined('FETCH_COLUMN')  || define('FETCH_COLUMN',  Query::FETCH_COLUMN);
defined('FETCH_KEY_PAIR') || define('FETCH_KEY_PAIR', Query::FETCH_KEY_PAIR);
