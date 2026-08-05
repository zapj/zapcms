<?php

namespace zap;

use zap\db\ZPDO;

class DB
{
    /** @var ZPDO[] Cached connections */
    protected static array $connections = [];

    /** @var ZPDO|null Cached connection used within transactions */
    protected static ?ZPDO $transactionConnection = null;

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
