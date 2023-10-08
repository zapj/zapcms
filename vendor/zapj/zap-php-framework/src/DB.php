<?php

namespace zap;

use Exception;
use PDO;
use PDOException;
use zap\db\Expr;
use zap\db\Query;
use zap\db\ZPDO;
use zap\util\Arr;

/**
 * @method static upsert($table, $data, $duplicate = null )
 * @method static int insert(string $table,array $data)
 * @method static replace($table, $data)
 * @method static update($table, $data, $conditions = '', $params = array())
 * @method static delete($table, $conditions = '', $params = array())
 * @method static int count($table, $conditions = '', $params = array())
 * @method static keyPair($table, $columns, $conditions = '', $params = array())
 * @method static int rowCount()
 * @method static string toSnakeCase($name)
 * @method static string prepareSQL($sql)
 * @method static quoteColumn($columnName)
 * @method static quoteTable($table)
 * @method static setFetchMode($mode)
 * @method static setAutoCommit($value)
 * @method static getAutoCommit()
 * @method static buildParams($array,$name)
 * @method static \PDOStatement statement($statement, $params = [])
 * @method static renameTable($oldName, $newName)
 * @method static dropTable($table)
 * @method static truncateTable($table)
 */
class DB
{

    /**
     * @var array 连接池
     */
    protected static $conn_pool = [];

    /**
     * @var string 默认连接名字
     */
    protected static $default_name;

    /**
     * 连接DB
     * @param $default_name string|null connection name
     * @return ZPDO
     * @throws Exception
     */
    public static function connect(string $default_name = null): ZPDO
    {
        if(is_null(static::$default_name)){
            static::$default_name = config("database.default");
        }
        if(is_null($default_name)){
            $default_name = static::$default_name;
        }
        if(isset(static::$conn_pool[$default_name])){
            return static::$conn_pool[$default_name];
        }

        $config = config("database.connections.{$default_name}");
        if(empty($config)){
            throw new Exception("could not find database config : {$default_name},Please check config/database.php");
        }

        static::$conn_pool[$default_name] = new ZPDO($config);
        return static::$conn_pool[$default_name];
    }

    /**
     * 获取PDO
     * @param string|null $default_name
     * @return ZPDO
     * @throws Exception
     */
    public static function getPDO(string $default_name = null): ZPDO
    {
        return static::connect($default_name);
    }

    /**
     * Quote
     * @param mixed $value
     * @return array|false|false[]|string|string[]
     * @throws Exception
     */
    public static function quote($value)
    {
        $pdo = static::connect(static::$default_name);
        if(is_array($value)){
            return array_map(function($value) use ($pdo){
                return $pdo->quote($value);
                },$value);
        }
        return $pdo->quote($value);
    }

    /**
     * 预处理SQL
     * @param string $statement
     * @param array $options
     * @return false|\PDOStatement
     * @throws Exception
     */
    public static function prepare(string $statement, array $options = [])
    {
        $pdo = static::connect(static::$default_name);
        return $pdo->prepare($pdo->prepareSQL($statement),$options);
    }

    /**
     * 执行SQL
     * @param string $statement
     * @return false|int
     * @throws Exception
     */
    public static function exec(string $statement)
    {
        $pdo = static::connect(static::$default_name);
        return $pdo->exec($pdo->prepareSQL($statement));
    }

    /**
     * query
     * @param string $statement
     * @param array $params
     * @return false|\PDOStatement
     * @throws Exception
     */
    public static function query(string $statement, array $params = [])
    {
        $stm = static::prepare($statement);
        $stm->execute($params);
        return $stm;
    }

    /**
     * value
     * @param string $statement
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public static function value(string $statement, array $params = []){
        $stm = static::prepare($statement);
        $stm->execute($params);
        return $stm->fetchColumn();
    }

    /**
     * getAll
     * @param string $statement
     * @param array $params
     * @return array|false
     * @throws Exception
     */
    public static function getAll(string $statement, array $params = [],$fetchMode = null)
    {
        $stm = static::prepare($statement);
        $stm->execute($params);
        return $stm->fetchAll($fetchMode);
    }

    /**
     * getRow
     * @param string $statement
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public static function getRow(string $statement, array $params = [],$fetchMode = null)
    {
        $stm = static::prepare($statement);
        $stm->execute($params);
        return $stm->fetch($fetchMode);
    }

    /**
     * @throws Exception
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::connect(static::$default_name),$name],$arguments);
    }

    /**
     * table model
     * @param string $table
     * @param string|null $alias
     * @return Query
     * @throws Exception
     */
    public static function table(string $table, string $alias = null): Query
    {
        $query = new Query(static::connect(static::$default_name));
        return $query->from($table,$alias);
    }

    /**
     * 开启事务
     * @param $callback \Closure
     * @param string|null $connection
     * @return bool 事务成功返回true
     * @throws Exception
     */
    public static function transaction(\Closure $callback, string $connection = null): bool
    {
        try{
            static::connect($connection)->beginTransaction();
            if(is_callable($callback)){
                $callback();
            }
            return static::connect($connection)->commit();
        }catch (PDOException $exception){
            static::connect($connection)->rollBack();
            return false;
        }
    }

    public static function connection($connection = null , $callback = null){

        $default_name = static::$default_name;
        static::$default_name = $connection;
        if(is_callable($callback)){
            $callback();
        }
        static::$default_name = $default_name;
    }

    public static function raw($value): Expr
    {
        return Expr::make($value);
    }

    public static function beginTransaction($connection = null): bool
    {
        $pdo = static::connect($connection);
        return $pdo->beginTransaction();
    }

    /**
     * 提交事务
     * @param string|null $connection DB连接名称
     * @return bool
     * @throws \Exception
     */
    public static function commit(string $connection = null): bool
    {
        $pdo = static::connect($connection);
        return $pdo->commit();
    }

    /**
     * 事务回滚
     * @param string|null $connection
     * @return bool
     * @throws Exception
     */
    public static function rollback(string $connection = null): bool
    {
        $pdo = static::connect($connection);
        return $pdo->rollBack();
    }


}