<?php

namespace zap\i18n;

use zap\traits\SingletonTrait;
use zap\util\Str;

class Language {

    use SingletonTrait;

    public $messages = [];
    public $language = 'zh_CN';
    public $languagePath = [ZAP_SRC . '/resources/languages'];

    /**
     *
     * @param string $key
     * @param null|mixed $value
     */
    public static function with($key, $value = null) {
        if (is_array($key)) {
            static::instance()->messages += $key;
        } else {
            static::instance()->messages[$key] = $value;
        }
    }

    /**
     * Language set
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        static::instance()->messages[$key] = $value;
    }

    /**
     * 获取语言
     * @param string $key
     * @return mixed
     */
    public static function get($key) {
        if (isset(static::instance()->messages[$key])) {
            return static::instance()->messages[$key];
        }
        return $key;
    }

    public static function trans($name,$params = null,$value = null){
        [$filename,$msgKey] = explode('.',$name);
        if (!isset(static::instance()->messages['lang.'.$filename])) {
            static::load($filename);
        }
        $message = static::get($msgKey);
        if(is_null($params)){
            return $message;
        }else if(!is_array($params)){
            $value = $params;
            $params = 'value';
        }
        return Str::format($message,$params,$value);
    }

    /**
     * @param $key
     * @return bool
     */
    public static function has($key) {
        return array_key_exists($key, static::instance()->messages);
    }

    /**
     * 加载语言文件
     * @param string $name
     */
    public static function load($name) {
        if (is_array(static::instance()->languagePath)) {
            $language = static::instance()->language;
            foreach (static::instance()->languagePath as $path) {
                $file = $path . "/{$language}/{$name}.php";
                if (file_exists($file)) {
                    $_LANG = include($file);
                    if (!is_null($_LANG) && is_array($_LANG)) {
                        static::instance()->messages = array_merge(static::instance()->messages,$_LANG);
                        static::instance()->messages['lang.'.$name] = true;
                    }
                }
            }
        }
    }

    /**
     * @param string $language
     */
    public static function useLanguage($language = 'zh-CN') {
        static::instance()->language = $language;
        static::addPath(resource_path('languages'));
    }

    /**
     * @param string $path
     */
    public static function addPath($path) {
        if(array_search($path,static::instance()->languagePath) === false){
            static::instance()->languagePath[] = $path;
        }
    }

    public static function getPaths() {
        return static::instance()->languagePath;
    }
}