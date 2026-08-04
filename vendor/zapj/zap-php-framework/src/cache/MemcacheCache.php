<?php

namespace zap\cache;

class MemcacheCache implements CacheInterface
{
    /**
     * @var \Memcache|\Memcached
     */
    protected $memcache;

    /**
     * @var string 驱动类型: 'memcache' 或 'memcached'
     */
    protected string $driver;

    /**
     * MemcacheCache 构造器
     *
     * @param array $options [
     *   'driver' => 'memcached',           // 'memcache' 或 'memcached'
     *   'servers' => [                     // 服务器列表
     *       ['host' => '127.0.0.1', 'port' => 11211, 'weight' => 1],
     *   ],
     *   'persistent_id' => 'app_cache',   // (仅 Memcached) 持久连接 ID
     *   'options' => [],                   // (仅 Memcached) 额外选项
     * ]
     */
    public function __construct($options = [])
    {
        $default = ['driver' => 'memcached'];
        $options += $default;

        $this->driver = $options['driver'];

        if ($this->driver === 'memcached') {
            if (!class_exists('\Memcached')) {
                throw new CacheException('Memcached extension is not loaded.');
            }
            $persistentId = $options['persistent_id'] ?? 'zap_cache';
            $this->memcache = new \Memcached($persistentId);

            // 仅在未连接时添加服务器
            if (empty($this->memcache->getServerList())) {
                $servers = $options['servers'] ?? [['host' => '127.0.0.1', 'port' => 11211, 'weight' => 1]];
                foreach ($servers as $server) {
                    $this->memcache->addServer(
                        $server['host'],
                        $server['port'] ?? 11211,
                        $server['weight'] ?? 1
                    );
                }
            }

            // 应用额外选项
            if (isset($options['options']) && is_array($options['options'])) {
                foreach ($options['options'] as $opt => $val) {
                    $this->memcache->setOption($opt, $val);
                }
            }
        } else {
            // Memcache 扩展
            if (!class_exists('\Memcache')) {
                throw new CacheException('Memcache extension is not loaded.');
            }
            $this->memcache = new \Memcache();
            $servers = $options['servers'] ?? [['host' => '127.0.0.1', 'port' => 11211]];
            foreach ($servers as $server) {
                $this->memcache->addServer(
                    $server['host'],
                    $server['port'] ?? 11211,
                    $options['persistent'] ?? true,
                    $server['weight'] ?? 1
                );
            }
        }
    }

    public function get($key, $default = null, $ttl = null)
    {
        $value = $this->memcache->get($key);

        // Memcached 返回 false 表示未命中（getResultCode 更精确）
        if ($this->driver === 'memcached') {
            if ($this->memcache->getResultCode() === \Memcached::RES_NOTFOUND) {
                if (is_callable($default)) {
                    $data = $default();
                    if ($data !== null) {
                        $this->set($key, $data, $ttl);
                        return $data;
                    }
                }
                return $default;
            }
        } elseif ($value === false) {
            if (is_callable($default)) {
                $data = $default();
                if ($data !== null) {
                    $this->set($key, $data, $ttl);
                    return $data;
                }
            }
            return $default;
        }

        if ($value !== false) {
            $data = @unserialize($value);
            return $data !== false ? $data : $value;
        }

        return $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        if (!is_string($value) && !is_numeric($value)) {
            $value = serialize($value);
        }

        $expire = $ttl ?? 0;

        if ($this->driver === 'memcached') {
            return $this->memcache->set($key, $value, $expire > 0 ? time() + $expire : 0);
        }

        return $this->memcache->set($key, $value, 0, $expire);
    }

    public function delete($key): bool
    {
        if ($this->driver === 'memcached') {
            $this->memcache->delete($key);
            return $this->memcache->getResultCode() !== \Memcached::RES_NOTFOUND;
        }
        return $this->memcache->delete($key);
    }

    public function clear(): bool
    {
        if ($this->driver === 'memcached') {
            return $this->memcache->flush();
        }
        return $this->memcache->flush();
    }

    public function getMultiple($keys, $default = null, $ttl = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, is_array($default) ? ($default[$key] ?? null) : $default, $ttl);
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
    }

    public function deleteMultiple($keys): bool
    {
        $allOk = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $allOk = false;
            }
        }
        return $allOk;
    }

    public function has($key): bool
    {
        return $this->get($key) !== null;
    }

    public function increment($key, $initValue = null)
    {
        $value = $this->get($key);
        if (is_null($value)) {
            $value = $initValue ?? 0;
        } else {
            $value = $initValue ? $value + $initValue : $value + 1;
        }
        $this->set($key, $value);
        return $value;
    }

    public function decrement($key, $initValue = null)
    {
        $value = $this->get($key);
        if (is_null($value)) {
            $value = $initValue ?? 0;
        } else {
            $value = $initValue ? $value - $initValue : $value - 1;
        }
        $this->set($key, $value);
        return $value;
    }

    public function pull($key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->delete($key);
        return $value;
    }

    /**
     * 获取原生连接实例
     *
     * @return \Memcache|\Memcached
     */
    public function getConnection()
    {
        return $this->memcache;
    }

    /**
     * 获取驱动类型标识
     *
     * @return string 'memcache' 或 'memcached'
     */
    public function getDriver(): string
    {
        return $this->driver;
    }
}
