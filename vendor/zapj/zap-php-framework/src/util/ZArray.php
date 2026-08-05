<?php

namespace zap\util;

use ArrayAccess;
use Countable;
use Exception;
use IteratorAggregate;
use Traversable;
use ArrayIterator;
use Serializable;

class ZArray implements IteratorAggregate, ArrayAccess, Serializable, Countable
{
    protected array $elements;

    public function __construct(array $input = [])
    {
        $this->elements = $input;
    }

    // ========== 魔术方法 ==========

    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    public function &__get($key)
    {
        return $this->offsetGet($key);
    }

    // ========== IteratorAggregate ==========

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->elements);
    }

    // ========== ArrayAccess ==========

    public function offsetExists($offset): bool
    {
        if (is_null($offset)) {
            return false;
        }
        if (is_array($this->elements) && array_key_exists($offset, $this->elements)) {
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

    public function &offsetGet($offset)
    {
        $notFound = null;
        if (is_null($offset)) {
            return $notFound;
        }
        if (is_array($this->elements) && array_key_exists($offset, $this->elements)) {
            return $this->elements[$offset];
        }
        $array = &$this->elements;
        $keys = explode('.', $offset);
        foreach ($keys as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $notFound;
            }
            $array = &$array[$segment];
        }
        return $array;
    }

    public function offsetSet($offset, $value): void
    {
        $keys = explode('.', $offset);
        $array = &$this->elements;
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array = &$array[$key];
        }
        $key = array_shift($keys);
        switch ($key) {
            case '$':
                $array[] = $value;
                break;
            case '^':
                array_unshift($array, $value);
                break;
            default:
                $array[$key] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->elements[$offset]);
        }
    }

    // ========== Countable ==========

    public function count(): int
    {
        return count($this->elements);
    }

    // ========== Serializable ==========

    public function serialize(): ?string
    {
        return serialize($this->__serialize());
    }

    public function unserialize($data): void
    {
        $this->__unserialize(unserialize($data));
    }

    /**
     * PHP 7.4+ 序列化支持
     */
    public function __serialize(): array
    {
        return ['elements' => $this->elements];
    }

    /**
     * PHP 7.4+ 反序列化支持
     */
    public function __unserialize(array $data): void
    {
        $this->elements = $data['elements'] ?? [];
    }

    // ========== 公开方法 ==========

    public function &get(string $key, $default = null)
    {
        if (!$this->offsetExists($key)) {
            return $default;
        }
        return $this->offsetGet($key);
    }

    public function set(string $key, $value): void
    {
        $this->offsetSet($key, $value);
    }

    public function has(string $key): bool
    {
        return $this->offsetExists($key);
    }

    /**
     * 获取底层数组副本
     */
    public function toArray(): array
    {
        return $this->elements;
    }

    /** @deprecated 使用 toArray() */
    public function copy(): array
    {
        return $this->elements;
    }

    /**
     * 合并数据（已有键值保留）
     */
    public function merge(array $data): void
    {
        $this->elements = array_merge($data, $this->elements);
    }

    /**
     * 合并数据（新值覆盖已有键值）
     */
    public function replace(array $data): void
    {
        $this->elements = array_merge($this->elements, $data);
    }

    /**
     * 获取所有元素
     */
    public function all(): array
    {
        return $this->elements;
    }
}
