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
 * @method static int upsert($table, $data, $duplicate = null )
 * @method static int insert(string $table,array $data)
 * @method static int replace($table, $data)
 * @method static int update($table, $data, $conditions = '', $params = array())
 * @method static int delete($table, $conditions = '', $params = array())
 * @method static int count($table, $conditions = '', $params = array())
 * @method static array keyPair($table, $columns, $conditions = '', $params = array())
 * @method static int rowCount()
 * @method static mixed value(string $statement, array $params = [])
 * @method static string toSnakeCase($name)
 * @method static string prepareSQL($sql)
 * @method static string quoteColumn($columnName)
 * @method static string quoteTable($table)
 * @method static mixed|array|string|integer quote($value,$type = null)
 * @method static false|\PDOStatement prepare($query, $options = [])
 * @method static array|false select($query, $params = [], ...$fetch_mode_args)
 * @method static false|\PDOStatement query($query, $params = [], ...$fetch_mode_args)
 * @method static bool setFetchMode($mode)
 * @method static bool setAutoCommit($value)
 * @method static bool getAutoCommit()
 * @method static buildParams($array,$name)
 * @method static Query table(string $table,$alias = null)
 * @method static \PDOStatement statement($statement, $params = [])
 * @method static false|int renameTable($oldName, $newName)
 * @method static false|int dropTable($table)
 * @method static false|int truncateTable($table)
 * @method static int rawExec($statement)
 * @method static mixed getAll(string $statement, array $params = [],$fetchMode = null)
 * @method static mixed get(string $statement, array $params = [],$fetchMode = null)
 * @method static array getTables()
 * @method static string getTableStructure(string $table)
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
     * @throws Exception
     */
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([static::connect(static::$default_name),$name],$arguments);
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
        if(!is_callable($callback)){
            return false;
        }
        try{
            static::connect($connection)->beginTransaction();
            $callback();
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

}