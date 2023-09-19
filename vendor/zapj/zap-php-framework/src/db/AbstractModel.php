<?php

namespace zap\db;

use zap\DB;
use zap\util\Arr;

use zap\util\Str;

use function app;


/**
 * @method static \zap\db\Query select($columns = '*')
 */
abstract class AbstractModel implements \ArrayAccess
{
    protected $primaryKey = 'id';

    protected $autoincrement = true;

    protected $table;

    protected $attributes = array();

    protected $connection;

    /**
     * Table Attributes
     * @param array $attributes
     */
    public function __construct(array $attributes = array(),$filterKeys = []) {
        $this->table = $this->table ?? static::getDefaultTableName();
        $this->fill($attributes,$filterKeys);
        $this->init();
    }

    public static function where($name,$operator = '=',$value = null){
        $query = DB::table(static::tableName());
        $query->asObject(get_called_class());
        return $query->where($name,$operator,$value);
    }

    public static function table($table,$alias = null){
        $query = DB::table($table,$alias);
        $query->asObject(get_called_class());
        return $query;
    }

    public function db($connection = null) {
        return DB::connect($this->connection ?? $connection);
    }

    public function init() {

    }

    public function getById($id , $fetchMode = null) {
        $primaryKey = $this->getPrimaryKey();
        $query = $this->asObject(get_called_class());
        if(is_array($primaryKey)){
            //多主键
            foreach($primaryKey as $key=>$value){
                $query->where($key,$value);
            }
            return $query->first($fetchMode);
        }
        $query->where($primaryKey,$id);
        return $query->first($fetchMode);

    }


    /**
     * Get TableName or Class Basename
     * @return string Table Name
     */
    public function getTable() {
        if (empty($this->table)) {
            $this->table = static::getDefaultTableName();
        }
        return $this->table;
    }

    /**
     * Get PrimaryKey Name
     * @return string
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    /**
     * Set PrimaryKey
     *
     * @param string $key
     *
     * @return AbstractModel
     */
    public function setPrimaryKey(string $key): AbstractModel
    {
        $this->primaryKey = $key;
        return $this;
    }

    /**
     *
     * @return int PrimaryKey Value
     */
    public function getId() {
        if(is_array($this->primaryKey)){
            return Arr::find($this->attributes,$this->primaryKey);
        }
        return $this->getAttribute($this->getPrimaryKey());
    }

    public function save() {
        if (empty($this->getId())) {
            $this->db()->insert($this->getTable(), $this->getAttributes());
            if($this->autoincrement){
                $this->setAttribute($this->getPrimaryKey(), $this->db()->lastInsertId());
            }

        } else {
            $query = DB::table($this->getTable())->set($this->getAttributes());

            $primaryKeyValue = $this->getId();
            if(is_array($primaryKeyValue)){
                foreach ($primaryKeyValue as $key=>$value){
                    $query->where($key,$value);
                }
            }else{
                $query->where($this->getPrimaryKey(), $this->getId());
            }
            $query->update();
        }
        return $this;
    }


    /**
     * fill attributes
     *
     * @param array $attributes
     *
     * @return AbstractModel
     */
    public function fill(array $attributes = array(), $filterKeys = []) {
        if(!empty($filterKeys)){
            $attributes = Arr::find($attributes,$filterKeys);
        }

        foreach ($attributes as $key => $value) {
            $this->offsetSet($key,$value);
        }
        return $this;
    }

    public function destory() {
        $id = $this->getId();
        if (!$id) {
            return false;
        }
        $pkey = $this->getPrimaryKey();
        if(is_array($id)){
            return $this->db()->delete($this->getTable(), $id);
        }
        return $this->db()->delete($this->getTable(), [$pkey=>$id]);
    }

    public function insert($data) {
        return DB::insert(static::getDefaultTableName(), $data);
    }

    public function batchInsert($rows) {
        $result = [];
        foreach($rows as $data){
            $result[] = DB::insert(static::tableName(), $data);
        }
        return $result;
    }

    /**
     * @param $params
     *
     * @return \zap\db\Query
     */
    public static function find($params = []) {
        $query = DB::table(static::tableName());
        $query->asObject(get_called_class());
        foreach($params as $name=>$value){
            $query->where($name,$value);
        }
        return $query;
    }

    public static function createQuery($params = []) {
        $query = DB::table(static::tableName());
        $query->asObject(get_called_class());
        foreach($params as $name=>$value){
            $query->where($name,$value);
        }
        return $query;
    }

    /**
     *
     * @param array|int $ids
     *
     * @return static|array|false|AbstractModel
     */
    public static function findById($ids,$fetchMode = null) {
        $model = new static;

        if(is_array($ids)){
            $query = $model->whereIn($model->getPrimaryKey(),$ids);
        }else{
            $query = $model->where($model->getPrimaryKey(),$ids);
        }

        if($fetchMode === null){
            $query->asObject(get_called_class());
        }
        if (is_numeric($ids)) {
            return $query->first($fetchMode);
        }
        return $query->get($fetchMode);
    }

    public static function findOne($ids,$fetchMode = null) {
        $model = new static;
        $query = $model->where($model->getPrimaryKey(),$ids);
        if($fetchMode === null){
            $query->asObject(get_called_class());
        }
        return $query->first($fetchMode);
    }

    public static function findAll($params = array(),$options = [] , $fetchMode = null) {
        $query = static::find($params);
        if(isset($options['orderBy'])){
            $query->orderBy($options['orderBy']);
        }
        if(isset($options['groupBy'])){
            $query->groupBy($options['groupBy']);
        }
        if(isset($options['limit'])){
            is_string($options['limit']) && $query->limit($options['limit']);
            is_array($options['limit']) && $query->limit($options['limit'][0],$options['limit'][1]);
        }
        return $query->get($fetchMode);
    }

    public static function updateAll($params = array(),$condition = []) {
        $query = static::createQuery();
        foreach ($condition as $key=>$where){
            if(is_int($key)){
                $query->where(...$where);
            }else{
                $query->where($key,$where);
            }
        }
        $query->set($params);
        return $query->update();
    }

    public static function count($columnName = '*',$condition = []) : int
    {
        $query = static::createQuery();
        foreach ($condition as $key=>$where){
            if(is_int($key)){
                $query->where(...$where);
            }else{
                $query->where($key,$where);
            }
        }
        return $query->count($columnName);
    }

    /**
     *
     * @param int|array $statement
     * @return int
     */
    public static function delete($statement) {
        $model = new static;
        $query = DB::table($model->getTable());
        if(is_array($statement) && Arr::isAssoc($statement)){
            foreach ($statement as $key=>$value){
                $query->where($key, $value);
            }
        } else if(is_array($statement)){
            $query->whereIn($model->getPrimaryKey(), $statement);
            return $query->delete();
        }else{
            $query->where($model->getPrimaryKey(), $statement);
        }

        return $query->delete();
    }


    /**
     *
     * @param array $attributes
     * @return \static
     */
    public static function create($attributes = [],$filterKeys = []) {
        $model = new static($attributes,$filterKeys);
        $model->save();
        return $model;
    }

    public static function model($attributes = [],$filterKeys = []) {
        $model = new static($attributes,$filterKeys);
        return $model;
    }

    /**
     * @param $attributes
     * @param $filterKeys
     *
     * @return static
     */
    public static function fromArray($attributes = [],$filterKeys = []) {
        $model = new static($attributes,$filterKeys);
        return $model;
    }

    public static function truncate() {
        DB::truncateTable(static::tableName());
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key) {
        return $this->attributes[$key];
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value) {
        $this->offsetSet($key,$value);
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        return $this->attributes[$offset];
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value) {
        if(isset($this->columnAlias[$offset])){
            $offset = $this->columnAlias[$offset];
        }
        $this->attributes[$offset] = $value;
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset) {
        unset($this->attributes[$offset]);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key) {
        return isset($this->attributes[$key]);
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key) {
        unset($this->attributes[$key]);
    }

    public function setAttribute($key, $value) {
        $this->offsetSet($key,$value);
        return $this;
    }

    public function hasAttribute($key) {
        return isset($this->attributes[$key]);
    }

    public function getAttribute($key) {
        if ($this->hasAttribute($key)) {
            return $this->attributes[$key];
        }
        return NULL;
    }

    public function getAttributes($keys = []) {
        if(!empty($keys)){
            return Arr::find($this->attributes,$keys);
        }

        return $this->attributes;
    }

    private function getClassName(): string
    {
        $className = explode('\\', get_class($this))[0];
        return strtolower($className);
    }

    private static function getDefaultTableName(): string
    {
        $array = explode('\\', get_called_class());
        $className = end($array);
        $className = preg_replace('/([A-Z])/', '_$1', $className);
        return strtolower(trim($className,'_'));
    }

    /**
     * 返回表名
     * @return string
     */
    public static function tableName(): string
    {
        return static::getDefaultTableName();
    }

    public function __call($name, $arguments)
    {
        $query = DB::table(static::tableName());
        $query->setFetchClass(get_called_class());
        return call_user_func_array([$query,$name],$arguments);
    }


    public static function __callStatic($method, $arguments)
    {
        $model = new static;
        if(method_exists($model,$method)){
            return $model->$method(...$arguments);
        }
        $query = DB::table(static::tableName());
        $query->asObject(get_called_class());
        if(Str::startsWith($method,'findBy')){
            $columnName = preg_replace('/([A-Z])/', '_$1', str_ireplace('findBy','',$method));
            $columnName = strtolower(trim($columnName,'_'));
            array_unshift($arguments,$columnName);
            return $query->where(...$arguments);
        }
        return $query->$method(...$arguments);
    }


}