<?php

namespace zap\db;

use zap\DB;
use zap\util\Arr;
use zap\util\Str;


/**
 * @method static select(string $tableName)
 */
abstract class Model implements \ArrayAccess
{
    protected $autoincrement = true;

    protected array $attributes = array();

    protected $connection;


    public function __construct(array $attributes = array(),$filterKeys = []) {
        $this->fill($attributes,$filterKeys);
        $this->init();
    }

    public static function where($name,$operator = '=',$value = null): Query
    {
        $query = DB::table(static::tableName());
        $query->asObject(get_called_class());
        return $query->where($name,$operator,$value);
    }

    public static function table($alias = null): Query
    {
        $query = DB::table(static::tableName(),$alias);
        $query->asObject(get_called_class());
        return $query;
    }

    public function db($connection = null): ZPDO
    {
        return DB::connect($this->connection ?? $connection);
    }

    public function init() {

    }

    public function getById($id , $fetchMode = null) {
        $primaryKey = static::primaryKey();
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
     * 获取主键ID
     * @return array|mixed|null
     */
    public function getId() {
        if(is_array(static::primaryKey())){
            return Arr::find($this->attributes,static::primaryKey());
        }
        return $this->getAttribute(static::primaryKey());
    }

    public function save() {
        if (empty($this->getId())) {
            $this->db()->insert(static::tableName(), $this->getAttributes());
            if ($this->autoincrement) {
                $this->setAttribute(static::primaryKey(), $this->db()->lastInsertId());
            }
        }else if(!$this->autoincrement){
                $this->db()->insert(static::tableName(), $this->getAttributes());
        } else {
            $query = DB::table(static::tableName())->set($this->getAttributes());

            $primaryKeyValue = $this->getId();
            if(is_array($primaryKeyValue)){
                foreach ($primaryKeyValue as $key=>$value){
                    $query->where($key,$value);
                }
            }else{
                $query->where(static::primaryKey(), $this->getId());
            }
            $query->update();
        }
        return $this;
    }


    /**
     * fill attributes
     *
     * @param array $attributes
     * @param array $filterKeys
     * @return Model
     */
    public function fill(array $attributes = array(), array $filterKeys = []): Model
    {
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
        $primaryKeyName = static::primaryKey();
        if(is_array($id)){
            return $this->db()->delete(static::tableName(), $id);
        }
        return $this->db()->delete(static::tableName(), [$primaryKeyName=>$id]);
    }

    public static function insert($data): int
    {
        return DB::insert(static::tableName(), $data);
    }

    public function batchInsert($rows): array
    {
        $result = [];
        foreach($rows as $data){
            $result[] = DB::insert(static::tableName(), $data);
        }
        return $result;
    }

    /**
     * @param array $params
     *
     * @return Query
     */
    public static function find(array $params = []): Query
    {
        $query = DB::table(static::tableName());
        $query->asObject(get_called_class());
        foreach($params as $name=>$value){
            if(is_int($name)){
                $query->where(...$value);
            }else{
                $query->where($name,$value);
            }
        }
        return $query;
    }

    public static function createQuery($conditions = []): Query
    {
        $query = DB::table(static::tableName());
        $query->asObject(get_called_class());
        foreach($conditions['where'] ?? [] as $name=>$value){
            if(is_int($name)){
                $query->where(...$value);
            }else{
                $query->where($name,$value);
            }
        }
        if(isset($conditions['limit'])){
            $query->limit(...$conditions['limit']);
        }

        if(isset($conditions['orderBy'])){
            $query->orderBy($conditions['orderBy']);
        }

        if(isset($conditions['groupBy'])){
            $query->orderBy($conditions['groupBy']);
        }

        return $query;
    }

    /**
     *
     * @param array|int $ids
     *
     * @return static|array|false|Model
     */
    public static function findById($ids,$fetchMode = null) {
        $query = static::table();

        if(is_array($ids)){
            $query = $query->whereIn(static::primaryKey(),$ids);
        }else{
            $query = $query->where(static::primaryKey(),$ids);
        }

        if($fetchMode === null){
            $query->asObject(get_called_class());
        }
        if (is_numeric($ids)) {
            return $query->first($fetchMode);
        }
        return $query->get($fetchMode);
    }

    /**
     * @param int|array $condition
     * @param $fetchMode
     * @return mixed
     */
    public static function findOne($condition,$fetchMode = null) {
        $query = static::table();
        if(is_int($condition)){
            $query = $query->where(static::primaryKey(),$condition);
        } else if(is_array($condition)){
            foreach ($condition as $key => $cond){
                if(is_int($key)){
                    $query->where(...$cond);
                }else{
                    $query->where($key,$cond);
                }
            }
        }

        if($fetchMode === null){
            $query->asObject(get_called_class());
        }
        return $query->first($fetchMode);
    }

    public static function findAll($params = [], $fetchMode = null) {
        $query = static::createQuery($params);
        return $query->get($fetchMode);
    }

    public static function updateAll($data = array(),$condition = []) {
        $query = static::find($condition);
        $query->set($data);
        return $query->update();
    }

    public static function count($columnName = '*',$condition = []) : int
    {
        $query = static::find($condition);
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
    public static function delete($statement): int
    {
        $query = DB::table(static::tableName());
        if(is_array($statement) && Arr::isAssoc($statement)){
            foreach ($statement as $key=>$value){
                $query->where($key, $value);
            }
        } else if(is_array($statement)){
            $query->whereIn(static::primaryKey(), $statement);
            return $query->delete();
        }else{
            $query->where(static::primaryKey(), $statement);
        }

        return $query->delete();
    }


    /**
     *
     * @param array $attributes
     * @param array $filterKeys
     * @return static
     */
    public static function create(array $attributes = [], array $filterKeys = []): Model
    {
        $model = new static($attributes,$filterKeys);
        $model->save();
        return $model;
    }

    public static function model($attributes = [],$filterKeys = []): Model
    {
        return new static($attributes,$filterKeys);
    }

    /**
     * @param array $attributes
     * @param array $filterKeys
     *
     * @return static
     */
    public static function fromArray(array $attributes = [], array $filterKeys = []): Model
    {
        return new static($attributes,$filterKeys);
    }

    public static function truncate() {
        DB::truncateTable(static::tableName());
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function __get($key) {
        return $this->attributes[$key];
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  mixed  $key
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

    public function setAttribute($key, $value): Model
    {
        $this->offsetSet($key,$value);
        return $this;
    }

    public function hasAttribute($key): bool
    {
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

    public static function primaryKey()
    {
        return 'id';
    }

    public function __call($name, $arguments)
    {
        $query = DB::table(static::tableName());
        $query->setFetchClass(get_called_class());
        return call_user_func_array([$query,$name],$arguments);
    }


    public static function __callStatic($method, $arguments)
    {
//        $model = new static;
//        if(method_exists($model,$method)){
//            return $model->$method(...$arguments);
//        }
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