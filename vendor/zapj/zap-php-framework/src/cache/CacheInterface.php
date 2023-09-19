<?php

namespace zap\cache;

interface CacheInterface
{

    public function get($key, $default = null, $ttl = null);

    public function set($key, $value, $ttl = null);

    public function delete($key);

    public function clear();

    public function getMultiple($keys, $default = null);

    public function setMultiple($values, $ttl = null);

    public function deleteMultiple($keys);

    public function has($key);
}