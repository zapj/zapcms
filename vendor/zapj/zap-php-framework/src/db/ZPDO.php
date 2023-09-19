<?php

namespace zap\db;

use PDO;
use PDOException;
use zap\exception\NotSupportedException;
use zap\util\Random;


class ZPDO extends PDO
{
    protected $tablePrefix = 'z_';

    protected $driver;

    public $rowCount = 0;

    public function __construct($dsn, $username = null, $password = null,
        $options = null
    ) {
        parent::__construct($dsn, $username, $password, $options);
        $this->driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->setAttribute(PDO::ATTR_STATEMENT_CLASS,[Statement::class]);
    }

    public function setTablePrefix($prefix){
        $this->tablePrefix = $prefix;
    }

    public function prepareSQL($sql){
        if($this->tablePrefix){
            return preg_replace_callback(
                '/(\\{(%?[\w\-\. ]+%?)\\}|\\[([\w\-\. ]+)\\])/',
                function ($matches) {
                    if (isset($matches[3])) {
                        return $this->quoteColumn($matches[3]);
                    } else {
                        return $this->quoteTable($matches[2]);
                    }
                }, $sql
            );
        }
        return $sql;
    }

    public function quoteColumn($columnName) {
        $colAlias = explode('.', $columnName);
        if (is_array($colAlias) && count($colAlias) == 2) {
            return $this->quoteColumn($colAlias[0]) . '.' . $this->quoteColumn($colAlias[1]);
        }
        switch ($this->getAttribute(PDO::ATTR_DRIVER_NAME)) {
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

    public function quoteTable($table) {
        $table = $this->tablePrefix . $table;
        switch ($this->getAttribute(PDO::ATTR_DRIVER_NAME)) {
            case 'mysql':
            case 'mariadb':
                return '`' . $table . '`';
            case 'mssql':
                return "[$table]";
            case 'pssql':
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

    public function setFetchMode($mode){
        $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE , $mode);
    }

    public function setAutoCommit($value) {
        $this->setAttribute(PDO::ATTR_AUTOCOMMIT, $value);
    }

    public function getAutoCommit() {
        return $this->getAttribute(PDO::ATTR_AUTOCOMMIT);
    }

    public function buildParams($array,$name){
        $params = [];
        $values = [];
        for($i = 0;$i<count($array);$i++){
            $params[$i] = ":{$name}{$i}";
            $values[$params[$i]] = $array[$i];
        }
        return [join(',',$params),$values];
    }

    public function statement($statement, $params = []) {
        $stm = $this->prepare($this->prepareSQL($statement));
        $stm->execute($params);
        $this->rowCount = $statement->rowCount();
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
     * @return int Return Last id
     */
    public function insert($table, $data) {
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

    public function upsert($table, $data, $duplicate = null ,$primaryKeys = null ) {
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
        }else if(!empty($duplicate) && $this->driver == 'pssql'){
            if(is_null($primaryKeys)){
                reset($data);
                $primaryKeys = key($data);
            }else if(is_array($primaryKeys)){
                $primaryKeys = join(',',$primaryKeys);
            }
            $sql .= ' ON CONFLICT ('.$primaryKeys.') DO UPDATE SET '.join(',',$dupSet);
        }else{
            throw new NotSupportedException('This method only supports mysql');
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
     * @return int Return Last id
     */
    public function replace($table, $data) {
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
     * @param $data 修改的数据
     * @param string $conditions 条件
     * @param array $params 参数
     * @return int
     */
    public function update($table, $data, $conditions = '', $params = array()) {
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
     * @param string $conditions 条件
     * @param array $params 参数
     * @return int
     */
    public function delete($table, $conditions = '', $params = array()) {
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
     * @param string $conditions 条件
     * @param array $params 参数
     * @return mixed|int 返回行数
     */
    public function count($table, $conditions = '', $params = array()) {
        $sql = 'SELECT COUNT(*) as rowcount FROM ' . $this->quoteTable($table);
        $input_params = array();
        if (($where = $this->prepareConditions($conditions, $params, $input_params)) != '') {
            $sql .= ' WHERE ' . $where;
        }
        $statement = $this->prepare($sql);
        $statement->execute($input_params);
        return $statement->fetchColumn(0);
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
    public function keyPair($table, $columns, $conditions = '', $params = array()) {
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

    public function toSnakeCase($name){
        $name = preg_replace('/([A-Z])/', '_$1', $name);
        return strtolower(trim($name,'_'));
    }


}