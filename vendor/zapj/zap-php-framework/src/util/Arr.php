<?php

namespace zap\util;

class Arr {
    public static function get($array, $key, $default = null) {
        if (is_null($key)) {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }
        return $array;
    }

    public static function find($array, $keys) {
       if(is_string($keys)){
           return isset($array[$keys]) ? $array[$keys] : [];
       }
       $items = [];
       foreach ($keys as $key) {
           if(isset($array[$key])){
               $items[$key] = $array[$key];
           }
        }
        return $items;
    }

    public static function has($array, $key) {
        if (empty($array) || is_null($key)) {
            return false;
        }
        if (array_key_exists($key, $array)) {
            return true;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return false;
            }
            $array = $array[$segment];
        }
        return true;
    }

    public static function set(&$array, $key, $value) {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = array();
            }
            $array = &$array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }

    public static function isAssoc(array $array) {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }
}