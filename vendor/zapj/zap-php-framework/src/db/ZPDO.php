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

    public function __construct($config) {
        $this->tablePrefix = $config['prefix'] ?? '';
        $this->driver = $config['driver'] ?? 'mysql';
        $dsn = $config['dsn'] ?? $this->buildDSN($config);
        $username = $config['user'] ?? null;
        $password = $config['password'] ?? null;
        $options  = array(
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => FALSE,
        );
        if($this->driver == 'mysql'){
            $db_charset = $config['charset'] ?? 'utf8';
            $db_collate = $config['collate'] ?? null;
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$db_charset} " . (is_null($db_collate) ?: " COLLATE $db_collate");
        }
        $options += Arr::get($config,'options',[]);
        parent::__construct($dsn, $username, $password, $options);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS,[Statement::class]);
    }

    private function buildDSN(&$config): string
    {
        $dsnElements = [];
        switch ($this->driver){
            case 'mysql':
            case 'mariadb':
                $dsnElements = Arr::find($config,['host','port','dbname','unix_socket','charset']);
                break;
            case 'pgsql':
                $dsnElements = Arr::find($config,['host','port','dbname','user','password','sslmode']);
                unset($config['user'],$config['password']);
                break;
            case 'sqlite':
                trigger_error('Please directly set the DSN parameters',E_USER_ERROR);
            default:
                trigger_error("{$this->driver} driver not supported",E_USER_ERROR);
        }
        return $this->driver . ':' . http_build_query($dsnElements,'',';');
    }

    public function prepare($query, $options = [])
    {
        return parent::prepare($this->prepareSQL($query), $options);
    }

    public function exec($statement)
    {
        return parent::exec($this->prepareSQL($statement));
    }

    public function query($query, $params = [], ...$fetch_mode_args)
    {
        $stm = parent::query($this->prepareSQL($query), ...$fetch_mode_args);
        $stm->execute($params);
        $this->rowCount = $stm->rowCount();
        return $stm;
    }

    public function select($query, $params = [], ...$fetch_mode_args)
    {
        $stm = parent::query($this->prepareSQL($query), ...$fetch_mode_args);
        $stm->execute($params);
        $this->rowCount = $stm->rowCount();
        return $stm->fetchAll();
    }

    /**
     * getAll
     * @param string $statement
     * @param array $params
     * @param null $fetchMode
     * @return array|false
     */
    public function getAll(string $statement, array $params = [],$fetchMode = null)
    {
        $stm = $this->prepare($statement);
        $stm->execute($params);
        return $stm->fetchAll($fetchMode);
    }

    /**
     * get
     * @param string $statement
     * @param array $params
     * @param null $fetchMode
     * @return mixed
     */
    public function get(string $statement, array $params = [],$fetchMode = null)
    {
        $stm = $this->prepare($statement);
        $stm->execute($params);
        return $stm->fetch($fetchMode);
    }


    /**
     * table Query ActiveRecord
     * @param string $table
     * @param string|null $alias
     * @return Query
     */
    public function table(string $table, string $alias = null): Query
    {
        $query = new Query($this);
        return $query->from($table,$alias);
    }

    public function rawExec($statement)
    {
        return parent::exec($statement);
    }


    public function value(string $statement, array $params = []){
        $stm = $this->prepare($statement);
        $stm->execute($params);
        return $stm->fetchColumn();
    }


    public function setTablePrefix($prefix){
        $this->tablePrefix = $prefix;
    }

    public function prepareSQL($sql){
        if($this->tablePrefix){
            return preg_replace_callback('/\{([\w\-\. ]+)\}/',
                function ($matches) {
                    return $this->quoteTable($matches[1]);
                }, $sql);
        }
        return $sql;
    }

    public function quoteColumn($columnName): string
    {
        $colAlias = explode('.', $columnName);
        if (is_array($colAlias) && count($colAlias) == 2) {
            return $this->quoteColumn($colAlias[0]) . '.' . $this->quoteColumn($colAlias[1]);
        }
        switch ($this->driver) {
            case 'mysql':
            case 'mariadb':
                return "`$columnName`";
            case 'mssql':
                return "[$columnName]";
            case 'pssql':
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
                return "[$table]";
            case 'pgsql':
                return '"' . $table . '"';
            default:
                return $table;
        }
    }

    public function info(): array
    {
        $key_names = [
            'server'     => PDO::ATTR_SERVER_INFO,
            'driver'     => PDO::ATTR_DRIVER_NAME,
            'client'     => PDO::ATTR_CLIENT_VERSION,
            'version'    => PDO::ATTR_SERVER_VERSION,
            'connection' => PDO::ATTR_CONNECTION_STATUS,
        ];

        foreach ($key_names as $key => $value) {
            try {
                $key_names[$key] = $this->getAttribute($value);
            } catch (PDOException $e) {
                $key_names[$key] = $e->getMessage();
            }
        }

        return $key_names;
    }

    public function setFetchMode($mode): bool
    {
        return $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE , $mode);
    }

    public function setAutoCommit($value): bool
    {
        return $this->setAttribute(PDO::ATTR_AUTOCOMMIT, $value);
    }

    public function getAutoCommit() {
        return $this->getAttribute(PDO::ATTR_AUTOCOMMIT);
    }

    public function buildParams($array,$name): array
    {
        $params = [];
        $values = [];
        for($i = 0;$i<count($array);$i++){
            $params[$i] = ":{$name}{$i}";
            $values[$params[$i]] = $array[$i];
        }
        return [join(',',$params),$values];
    }

    /**
     * @param $statement
     * @param array $params
     * @return false|\PDOStatement
     */
    public function statement($statement, array $params = []) {
        $stm = $this->prepare($this->prepareSQL($statement));
        $stm->execute($params);
        $this->rowCount = $stm->rowCount();
        return $stm;
    }

    public function renameTable($oldName, $newName) {
        return $this->exec('RENAME TABLE ' . $this->quoteTable($oldName) . ' TO ' . $this->quoteTable($newName));
    }

    public function dropTable($table) {
        return $this->exec('DROP TABLE ' . $this->quoteTable($table));
    }

    public function truncateTable($table) {
        return $this->exec('TRUNCATE TABLE ' . $this->quoteTable($table));
    }

    /**
     * Db insert
     * @param string $table 表名
     * @param array $data 插入的数据
     * @return false|string LastID
     */
    public function insert(string $table, array $data) {
        $params = array();
        $names = array();
        $placeholders = array();
        foreach ($data as $name => $value) {
            $names[] = $this->quoteColumn($name);
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bindName = Random::rand(Random::ALPHA);
                $placeholders[] = ':' . $bindName;
                $params[$bindName] = $value;
            }
        }
        $sql = 'INSERT INTO ' . $this->quoteTable($table)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';
        $statement = $this->prepare($sql);
        $statement->execute($params);
        $this->rowCount = $this->lastInsertId();
        return $this->lastInsertId();
    }

    /**
     * @throws NotSupportedException
     */
    public function upsert($table, $data, $duplicate = null , $primaryKeys = null ) {
        $params = array();
        $names = array();
        $placeholders = array();

        foreach ($data as $name => $value) {
            $name = $this->quoteColumn($name);
            $names[] = $name;
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bindName = Random::rand(Random::ALPHA);
                $placeholders[] = ':' . $bindName;
                $params[$bindName] = $value;
            }
        }
        if($duplicate){
            $dupSet = [];
            foreach ($duplicate as $name => $value) {
                if ($value instanceof Expr) {
                    $dupSet[] = $this->quoteColumn($name) . '=' . $value->raw;
                } else {
                    $dupSet[] = $this->quoteColumn($name) . '=:' . $name;
                    $params[$name] = $value;
                }
            }
        }


        $sql = 'INSERT INTO ' . $this->quoteTable($table)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';

        if(!empty($duplicate) && $this->driver == 'mysql'){
            $sql .= ' ON DUPLICATE KEY UPDATE '.join(',',$dupSet);
        }else if(!empty($duplicate) && $this->driver == 'pgsql'){
            if(is_null($primaryKeys)){
                reset($data);
                $primaryKeys = key($data);
            }else if(is_array($primaryKeys)){
                $primaryKeys = join(',',$primaryKeys);
            }
            $sql .= ' ON CONFLICT ('.$primaryKeys.') DO UPDATE SET '.join(',',$dupSet);
        }else{
            throw new NotSupportedException('This method only supports mysql/pssql');
        }
        $statement = $this->prepare($sql);
        $statement->execute($params);
        $this->rowCount = $this->lastInsertId();
        return $this->lastInsertId();
    }

    /**
     * Db replace
     * @param string $table 表名
     * @param array $data 插入的数据
     * @return int LastID
     */
    public function replace(string $table, array $data) {
        $params = [];
        $names = [];
        $placeholders = [];
        foreach ($data as $name => $value) {
            $names[] = $this->quoteColumn($name);
            if ($value instanceof Expr) {
                $placeholders[] = $value->raw;
            } else {
                $bindName = Random::rand(Random::ALPHA);
                $placeholders[] = ':' . $bindName;
                $params[$bindName] = $value;
            }
        }
        $sql = 'REPLACE INTO ' . $this->quoteTable($table)
            . ' (' . implode(', ', $names) . ') VALUES ('
            . implode(', ', $placeholders) . ')';
        $statement = $this->prepare($sql);
        $statement->execute($params);
        $this->rowCount = $statement->rowCount();
        return $statement->rowCount();
    }

    /**
     * Db update
     * @param string $table 表名
     * @param array $data 修改的数据
     * @param string|array $conditions 条件
     * @param array $params 参数
     * @return int
     */
    public function update(string $table, array $data, $conditions = '', array $params = array()): int
    {
        $placeholders = array();
        $input_params = array();
        foreach ($data as $name => $value) {
            if ($value instanceof Expr) {
                $placeholders[] = $this->quoteColumn($name) . '=' . $value->raw;
            } else {
                $placeholders[] = $this->quoteColumn($name) . '=:' . $name;
                $input_params[$name] = $value;
            }
        }

        $sql = 'UPDATE ' . $this->quoteTable($table) . ' SET ' . implode(', ', $placeholders);
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql .= ' WHERE ' . $where;
        }
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        $this->rowCount = $statement->rowCount();
        return $statement->rowCount();
    }

    /**
     * db delete
     * @param string $table 表名
     * @param string|array $conditions 条件
     * @param array $params 参数
     * @return int
     */
    public function delete(string $table, $conditions = '', array $params = array()): int
    {
        $sql = 'DELETE FROM ' . $this->quoteTable($table);
        $input_params = array();
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql .= ' WHERE ' . $where;
        }
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        $this->rowCount = $statement->rowCount();
        return $statement->rowCount();
    }

    /**
     * db count
     * @param string $table 表名
     * @param string|array $conditions 条件
     * @param array $params 参数
     * @return mixed|int 返回行数
     */
    public function count(string $table, $conditions = '', array $params = array()) {
        $sql = 'SELECT COUNT(*) as rowcount FROM ' . $this->quoteTable($table);
        $input_params = array();
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql .= ' WHERE ' . $where;
        }
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        return $statement->fetchColumn();
    }

    /**
     * Db key_pair
     * 获取两列，第一列为key，第二列为value
     * @param string $table 表名
     * @param string|array $columns 列名
     * @param string $conditions 条件
     * @param array $params 参数
     * @return array
     */
    public function keyPair(string $table, $columns, $conditions = '', $params = array()) {
        if (is_array($columns)) {
            $columns = join(',', array_map(function ($value) {
                return $this->quoteColumn($value);
            }, $columns));
        }
        $sql = 'SELECT ' . $columns . ' FROM ' . $this->quoteTable($table);
        $input_params = array();
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql .= ' WHERE ' . $where;
        }
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        $this->rowCount = $statement->rowCount();
        return $statement->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    private function prepareConditions($conditions, $params, &$input_params) {
        if (is_array($conditions)) {
            $lines = array();
            $i = 0;
            foreach ($conditions as $name => $value) {
                if ($value instanceof Expr) {
                    $lines[] = $this->quoteColumn($name) . '=' . $value->raw;
                } else {
                    $bindName = Random::rand(Random::ALPHA);
                    $lines[] = $this->quoteColumn($name) . '=:' . $bindName . $i;
                    $input_params[$bindName . $i] = $value;
                }
                $i++;
            }
            return implode(' AND ', $lines);
        } else if (is_string($conditions) && is_array($params) && is_assoc($params)) {
            foreach ($params as $name => $value) {
                if ($value instanceof Expr) {
                    $input_params[$name] = $value->raw;
                }
                $input_params[$name] = $value;
            }
            return $conditions;
        } else if (is_string($conditions) && ( is_scalar($params) || (is_array($params) && !is_assoc($params)))) {
            if (is_scalar($params)) {
                $params = array($params);
            }
            $input_params += $params;
            return $conditions;
        }
        return '';
    }

    public function rowCount(){
        return $this->rowCount;
    }

    public function toSnakeCase($name): string
    {
        $name = preg_replace('/([A-Z])/', '_$1', $name);
        return strtolower(trim($name,'_'));
    }

    public function quote($value, $type = PDO::PARAM_STR)
    {
        if(is_array($value)){
            return array_map(function($value) {
                return $this->quote($value);
            },$value);
        }
        return parent::quote($value, $type);
    }




}