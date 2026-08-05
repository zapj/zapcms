<?php

namespace zap\cache;

class FileCache implements CacheInterface
{
    protected $cacheDir;

    protected string $isCache = 'enabled';

    public function __construct($options = null)
    {
        if(!isset($options['cacheDir'])){
            throw new CacheException('unset cacheDir');
        }

        $this->cacheDir = $options['cacheDir'];
        $this->isCache = $options['isCache'] == 'enabled';

        if(!is_dir($this->cacheDir)){
            throw new CacheException('invalid cacheDir "'.$this->cacheDir.'"');
        }

        if(!is_writable($this->cacheDir)){
            throw new CacheException('cacheDir is read-only. permissions?');
        }
    }

    public function get($key, $default = null, $ttl = null)
    {
        $filename = $this->getFilePath($key);

        if(is_file($filename) && $this->isCache){
            $content = file_get_contents($filename);

            if(!empty($content)){
                $data = unserialize($content);

                if($data === false || $ttl !== $data->ttl){
                    $data->ttl = 0;
                }

                if($data->ttl === null || $data->ttl > time()){
                    return $data->content;
                }

                unlink($filename);
            }

        }
        if(is_callable($default)){
            $data = $default();
            if($data !== null){
                $this->set($key,$data,$ttl);
                return $data;
            }
        }

        return $default;
    }

    public function set($key, $value, $ttl = null) {

        $file = $this->getFilePath($key);
        $dirPath  = dirname($file);

        if(!is_dir($dirPath)){
            mkdir($dirPath, 0755, true);
        }

        $data          = new \stdClass;
        $data->ttl     = $ttl ? time() + $ttl : null;
        $data->content = $value;

        return file_put_contents($file, serialize($data));
    }

    public function delete($key)
    {
        $filename = $this->getFilePath($key);

        if(is_file($filename)){
            return unlink($filename);
        }

        return false;
    }

    public function clear()
    {
        $iterator = new \RecursiveDirectoryIterator($this->cacheDir, \FilesystemIterator::CURRENT_AS_PATHNAME|\FilesystemIterator::SKIP_DOTS);

        foreach(new \RecursiveIteratorIterator($iterator) as $path){
            unlink($path);
        }

        return true;
    }

    public function getMultiple($keys, $default = null,$ttl = null)
    {
        $items = [];
        foreach ($keys as $key){
            $items[$key] = $this->get($key,is_array($default) ? $default[$key] : $default,$ttl);
        }
        return $items;
    }

    public function setMultiple($values, $ttl = null) {
        foreach($values as $key => $value){
            $this->set($key,$value,$ttl);
        }
    }

    public function deleteMultiple($keys)
    {
        foreach ($keys as $key){
            $this->delete($key);
        }
    }

    public function has($key)
    {
        return $this->get($key) !== null;
    }

    protected function getFilePath($key){
        $hash = hash('sha1', $key);
        return $this->cacheDir.DIRECTORY_SEPARATOR.$hash[0].$hash[1].DIRECTORY_SEPARATOR.$hash;
    }

    public function increment($key, $initValue = null){
        $value = $this->get($key);
        if(is_null($value)){
            $value = $initValue ?? 0;
        }else{
            $value = $initValue ? $value+$initValue : $value+1;
        }
        $this->set($key,$value);
        return $value;
    }

    public function decrement($key, $initValue = null){
        $value = $this->get($key);
        if(is_null($value)){
            $value = $initValue ?? 0;
        }else{
            $value = $initValue ? $value-$initValue : $value-1;
        }
        $this->set($key,$value);
        return $value;
    }

    public function pull($key,$default = null){
        $value = $this->get($key,$default);
        $this->delete($key);
        return $value;
    }

}