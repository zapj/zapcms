<?php

namespace zap;

use zap\db\Expr;
use zap\db\ZPDO;

class DB
{
    /** @var ZPDO[] Cached connections */
    protected static array $connections = [];

    /** @var ZPDO|null Cached connection used within transactions */
    protected static ?ZPDO $transactionConnection = null;

    /** @var string|null Default fetch model class for fetch() / fetchAll() */
    protected static ?string $fetchModel = null;

    // ==================================================================
    //  连接管理
    // ==================================================================

    /**
     * 获取数据库连接（按名称缓存）
     *
     * @param string|null $name 连接名，null 使用默认
     */
    public static function connection(?string $name = null): ZPDO
    {
        $name ??= 'default';

        if (self::$transactionConnection !== null) {
            return self::$transactionConnection;
        }

        if (!isset(self::$connections[$name])) {
            $dbConfig = config('database.connections.' . $name)
                ?? config('database.connections.default')
                ?? config('database.' . $name)
                ?? config('database.default')
                ?? [];
            self::$connections[$name] = new ZPDO($dbConfig);
        }

        return self::$connections[$name];
    }

    /** @deprecated 使用 connection() */
    public static function getConnection(?string $name = null): ZPDO
    {
        return self::connection($name);
    }

    /**
     * 获取 PDO 连接实例（供 Schema / TableSchema 等 schema 构建器使用）
     */
    public static function getPDO(?string $name = null): ZPDO
    {
        return self::connection($name);
    }

    // ==================================================================
    //  Fetch Model 配置
    // ==================================================================

    /**
     * 设置 fetch() / fetchAll() 默认使用的模型类。
     *
     * 设置后，fetch() 和 fetchAll() 会自动将查询结果水合到指定模型实例中。
     * 调用 fetch() / fetchAll() 时传入第三个参数可覆盖此默认值。
     *
     * @param string|null $modelClass 模型类名，传入 null 清除设置
     * @throws \InvalidArgumentException 如果类不存在
     */
    public static function setFetchModel(?string $modelClass): void
    {
        if ($modelClass !== null && !class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class [{$modelClass}] does not exist.");
        }
        static::$fetchModel = $modelClass;
    }

    /**
     * 获取当前默认 fetch model 类名。
     */
    public static function getFetchModel(): ?string
    {
        return static::$fetchModel;
    }

    // ==================================================================
    //  事务
    // ==================================================================

    public static function beginTransaction(): void
    {
        $conn = self::connection();
        $conn->beginTransaction();
        self::$transactionConnection = $conn;
    }

    public static function commit(): void
    {
        if (self::$transactionConnection !== null) {
            self::$transactionConnection->commit();
            self::$transactionConnection = null;
        }
    }

    public static function rollBack(): void
    {
        if (self::$transactionConnection !== null) {
            self::$transactionConnection->rollBack();
            self::$transactionConnection = null;
        }
    }

    public static function transaction(callable $callback, ...$args)
    {
        try {
            self::beginTransaction();
            $result = $callback(self::connection(), ...$args);
            self::commit();
            return $result;
        } catch (\Throwable $e) {
            self::rollBack();
            throw $e;
        }
    }

    // ==================================================================
    //  Query Builder
    // ==================================================================

    /** 获取 Query Builder 实例 */
    public static function table(string $table, ?string $alias = null): \zap\db\Query
    {
        return self::connection()->table($table, $alias);
    }

    // ==================================================================
    //  表达式（Raw Expression）
    // ==================================================================

    /**
     * 创建原始表达式，禁止参数绑定，直接拼入 SQL。
     *
     * 用于 Query Builder 的 set/where 等需要原始表达式的场景。
     *
     * @param string $value 原始 SQL 片段
     * @return Expr
     *
     * @example
     *   // 更新时自增计数器
     *   DB::table('posts')->where('id', 1)->update(['hits' => DB::raw('hits + 1')]);
     *
     *   // where 中使用函数
     *   DB::table('users')->where('created_at', '>', DB::raw('NOW()'))->getAll();
     *
     *   // CRUD 快捷方法中使用
     *   DB::update('posts', ['hits' => DB::raw('hits + 1'), 'updated_at' => DB::raw('NOW()')], ['id' => 1]);
     */
    public static function raw(string $value): Expr
    {
        return new Expr($value);
    }

    // ==================================================================
    //  CRUD 便捷方法  (表名 + 数据 模式)
    // ==================================================================

    /**
     * 插入一行数据
     *
     * @param string $table  表名
     * @param array  $data   字段 => 值 的关联数组
     * @return false|string  成功返回新 ID，失败返回 false
     */
    public static function insert(string $table, array $data)
    {
        return self::connection()->insert($table, $data);
    }

    /**
     * 批量插入
     *
     * @param string $table  表名
     * @param array  $rows   二维关联数组
     * @return int  受影响行数
     */
    public static function batchInsert(string $table, array $rows): int
    {
        return self::connection()->batchInsert($table, $rows);
    }

    /**
     * 插入或更新（存在则更新）
     *
     * @param string $table   表名
     * @param array  $data    插入数据
     * @param array  $duplicate  冲突时更新的字段
     * @param mixed  $primaryKeys 主键（仅 pgsql 需要）
     */
    public static function upsert(string $table, array $data, ?array $duplicate = null, $primaryKeys = null)
    {
        return self::connection()->upsert($table, $data, $duplicate, $primaryKeys);
    }

    /**
     * REPLACE INTO 操作
     */
    public static function replace(string $table, array $data): int
    {
        return self::connection()->replace($table, $data);
    }

    /**
     * 更新数据
     *
     * @param string       $table       表名
     * @param array        $data        要更新的字段数据
     * @param array|string $conditions  条件：关联数组（col=>val）或 SQL 字符串
     * @param array        $params      SQL 条件字符串的绑定参数
     * @return int  受影响行数
     */
    public static function update(string $table, array $data, $conditions = '', array $params = []): int
    {
        return self::connection()->update($table, $data, $conditions, $params);
    }

    /**
     * 删除数据
     *
     * @param string       $table       表名
     * @param array|string $conditions  条件：关联数组 或 SQL 字符串
     * @param array        $params      条件字符串的绑定参数
     * @return int  受影响行数
     */
    public static function delete(string $table, $conditions = '', array $params = []): int
    {
        return self::connection()->delete($table, $conditions, $params);
    }

    /**
     * 统计行数
     */
    public static function count(string $table, $conditions = '', array $params = [])
    {
        return self::connection()->count($table, $conditions, $params);
    }

    /**
     * 键值对查询
     */
    public static function keyPair(string $table, $columns, $conditions = '', array $params = []): array
    {
        return self::connection()->keyPair($table, $columns, $conditions, $params);
    }

    // ==================================================================
    //  原始 SQL 执行
    // ==================================================================

    /**
     * 执行原始 DML 语句（UPDATE / DELETE / DDL），返回受影响行数
     *
     * @param string $query  SQL 语句（支持 {table} 前缀占位）
     * @param array  $params 绑定参数
     * @return int  受影响行数
     */
    public static function exec(string $query, array $params = []): int
    {
        $stm = self::connection()->prepare($query);
        $stm->execute($params);
        return $stm->rowCount();
    }

    /**
     * 执行原始 INSERT 语句，返回新自增 ID
     *
     * @param string $query  SQL 语句
     * @param array  $params 绑定参数
     * @return false|string  成功返回新 ID
     */
    public static function execInsert(string $query, array $params = [])
    {
        $conn = self::connection();
        if (stripos(trim($query), 'INSERT') === 0) {
            $conn->exec($conn->prepareSQL($query));
            return $conn->lastInsertId();
        }
        $stm = $conn->prepare($query);
        $stm->execute($params);
        return $conn->lastInsertId();
    }

    /**
     * 执行原始 SQL 并返回 Statement（SELECT / 任意）
     */
    public static function statement(string $query, array $params = [])
    {
        return self::connection()->statement($query, $params);
    }

    /**
     * 执行原始 SQL 语句（SELECT / 任意），返回受影响行数
     */
    public static function query(string $query, array $params = []): \zap\db\Statement
    {
        $stm = self::connection()->prepare($query);
        $stm->execute($params);
        return $stm;
    }

    /**
     * 执行 SELECT 并返回所有结果
     */
    public static function select(string $query, array $params = [], int $fetchMode = \PDO::FETCH_ASSOC): array
    {
        return self::connection()->select($query, $params, $fetchMode);
    }

    // ==================================================================
    //  fetch / fetchAll（支持 fetch_model 水合到模型对象）
    // ==================================================================

    /**
     * 执行 SELECT 并返回单行结果。
     *
     * 支持 fetch_mode 与 fetch_model 两种用法，优先级：参数 > setFetchModel()。
     *
     * @param string            $query            SQL 语句
     * @param array             $params           绑定参数
     * @param string|int|null   $fetchModeOrModel PDO 常量 或 模型类名。null 时使用 setFetchModel() 设置的值
     * @return array|object|null  返回关联数组、对象或模型实例；无结果时返回 null
     *
     * @example
     *   // 数组模式
     *   $user = DB::fetch('SELECT * FROM users WHERE id = ?', [1]);
     *
     *   // 取单个值
     *   $count = DB::fetch('SELECT COUNT(*) FROM users', [], \PDO::FETCH_COLUMN);
     *
     *   // 水合到模型
     *   $user = DB::fetch('SELECT * FROM users WHERE id = ?', [1], User::class);
     *
     *   // 全局设置后自动水合
     *   DB::setFetchModel(User::class);
     *   $user = DB::fetch('SELECT * FROM users WHERE id = ?', [1]);
     */
    public static function fetch(string $query, array $params = [], $fetchModeOrModel = null)
    {
        [$fetchMode, $modelClass] = static::resolveFetchMode($fetchModeOrModel);

        $stm = self::connection()->prepare($query);
        $stm->execute($params);

        if ($modelClass) {
            $row = $stm->fetch(\PDO::FETCH_ASSOC);
            return $row !== false ? static::hydrate($modelClass, $row) : null;
        }

        $result = $stm->fetch($fetchMode);
        return $result !== false ? $result : null;
    }

    /**
     * 执行 SELECT 并返回所有行。
     *
     * 支持 fetch_mode 与 fetch_model 两种用法，优先级：参数 > setFetchModel()。
     *
     * @param string            $query            SQL 语句
     * @param array             $params           绑定参数
     * @param string|int|null   $fetchModeOrModel PDO 常量 或 模型类名。null 时使用 setFetchModel() 设置的值
     * @return array  始终返回数组（无结果时为空数组）
     *
     * @example
     *   // 数组模式
     *   $users = DB::fetchAll('SELECT * FROM users WHERE status = ?', [1]);
     *
     *   // 取单列值列表
     *   $ids = DB::fetchAll('SELECT id FROM users', [], \PDO::FETCH_COLUMN);
     *
     *   // 水合到模型
     *   $users = DB::fetchAll('SELECT * FROM users', [], User::class);
     *
     *   // 全局设置后自动水合
     *   DB::setFetchModel(User::class);
     *   $users = DB::fetchAll('SELECT * FROM users');
     */
    public static function fetchAll(string $query, array $params = [], $fetchModeOrModel = null): array
    {
        [$fetchMode, $modelClass] = static::resolveFetchMode($fetchModeOrModel);

        $rows = self::connection()->select(
            $query,
            $params,
            $modelClass ? \PDO::FETCH_ASSOC : $fetchMode
        );

        if ($modelClass) {
            return array_map(fn($row) => static::hydrate($modelClass, (array) $row), $rows);
        }

        return $rows;
    }

    /**
     * 解析 fetch 模式：返回 [PDO 常量, 模型类名|null]。
     *
     * 解析规则：
     *  - 传入 PDO 常量（int） → 直接使用
     *  - 传入类名字符串     → 使用 FETCH_ASSOC + hydrate 到模型
     *  - 传入 null           → 使用 setFetchModel() 设置的值；若未设置则默认 FETCH_ASSOC
     *
     * @param string|int|null $fetchModeOrModel
     * @return array{0: int, 1: string|null}
     */
    protected static function resolveFetchMode($fetchModeOrModel): array
    {
        $fetchMode  = \PDO::FETCH_ASSOC;
        $modelClass = null;

        if ($fetchModeOrModel === null) {
            $fetchModeOrModel = static::$fetchModel;
        }

        if (is_string($fetchModeOrModel) && class_exists($fetchModeOrModel)) {
            $modelClass = $fetchModeOrModel;
        } elseif (is_int($fetchModeOrModel)) {
            $fetchMode = $fetchModeOrModel;
        } elseif ($fetchModeOrModel !== null) {
            // 无效值，忽略
        }

        return [$fetchMode, $modelClass];
    }

    /**
     * 将一行关联数组数据水合到指定模型实例中。
     *
     * @param string $modelClass 模型类名
     * @param array  $attributes 字段 => 值
     * @return object  模型实例
     */
    protected static function hydrate(string $modelClass, array $attributes): object
    {
        return new $modelClass($attributes);
    }

    // ==================================================================
    //  查询日志
    // ==================================================================

    public static function enableQueryLog(): void
    {
        self::connection()->enableQueryLog();
    }

    public static function disableQueryLog(): void
    {
        self::connection()->disableQueryLog();
    }

    public static function getQueryLog(): array
    {
        return self::connection()->getQueryLog();
    }

    public static function flushQueryLog(): void
    {
        self::connection()->flushQueryLog();
    }

    // ==================================================================
    //  信息
    // ==================================================================

    public static function connectionInfo(): array
    {
        return self::connection()->info();
    }
}
