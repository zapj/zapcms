<?php

namespace zap\util;

use ArrayAccess;
use Countable;
use Exception;
use IteratorAggregate;
use Serializable;
use Traversable;
use ArrayIterator;

class ZArray implements IteratorAggregate, ArrayAccess, Serializable, Countable
{
    protected $elements;

    public function __construct($input = []){
        $this->elements = $input;
    }

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __set($key, $value)
    {
        return $this->offsetSet($key, $value);
    }

    public function &__get($key)
    {
        return $this->offsetGet($key);


    }

    /**
     * Retrieve an external iterator
     *
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @throws Exception on failure.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->elements);
    }

    /**
     * Whether a offset exists
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param  mixed  $offset  <p>
     *                         An offset to check for.
     *                         </p>
     *
     * @return bool true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        if (is_null($offset)) {
            return false;
        }
        if (isset($this->elements[$offset])) {
            return true;
        }
        $array = $this->elements;
        foreach (explode('.', $offset) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }
        return true;

    }

    /**
     * Offset to retrieve
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param  mixed  $offset  <p>
     *                         The offset to retrieve.
     *                         </p>
     *
     * @return mixed Can return all value types.
     */
    public function &offsetGet($offset)
    {
        $ret = null;
        if (is_null($offset)) {
            return $ret;
        }
        if (isset($this->elements[$offset])) {
            return $this->elements[$offset];
        }
        $array = $this->elements;
        foreach (explode('.', $offset) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $ret;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    /**
     * Offset to set
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param  mixed  $offset  <p>
     *                         The offset to assign the value to.
     *                         </p>
     * @param  mixed  $value   <p>
     *                         The value to set.
     *                         </p>
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            return ;
        }

        $keys = explode('.', $offset);
        $array = &$this->elements;
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
//        print_r($array);
//        $this->elements[array_shift($keys)] = $value;

//        $this->elements[$offset] = $value;
    }

    /**
     * Offset to unset
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param  mixed  $offset  <p>
     *                         The offset to unset.
     *                         </p>
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            unset($this->elements[$offset]);
        }
    }

    /**
     * String representation of object.
     *
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string|null The string representation of the object or null
     * @throws Exception Returning other type than string or null
     */
    public function serialize()
    {
        return serialize(get_object_vars($this));
    }

    /**
     * Constructs the object.
     *
     * @link https://php.net/manual/en/serializable.unserialize.php
     *
     * @param  string  $data  The string representation of the object.
     *
     * @return void
     */
    public function unserialize($data)
    {
        $ar = unserialize($data);
        $this->elements = $ar['elements'];
    }

    /**
     * Count elements of an object
     *
     * @link https://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * <p>
     * The return value is cast to an integer.
     * </p>
     */
    public function count(){
        return count($this->elements);
    }

    public function &get($key,$default = null){
        $ret = $this->offsetGet($key);
        if(is_null($ret)){
            return $default;
        }
        return $ret;
    }

    public function set($key,$value){
        return $this->offsetSet($key,$value);
    }

    public function has($key){
        return $this->offsetExists($key);
    }

    /**
     * 创建一个Copy
     * @return array
     */
    public function copy()
    {
        return $this->elements;
    }

    /**
     * 如果键值已存在，则跳过
     * @param $data
     *
     * @return void
     */
    public function merge($data){
        $this->elements = array_merge($data,$this->elements);
    }

    /**
     * 如果键值已存在，则替换新值
     * @param $data
     *
     * @return void
     */
    public function replace($data){
        $this->elements = array_merge($this->elements,$data);
    }
}