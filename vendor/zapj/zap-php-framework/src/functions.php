<?php


use zap\http\Response;
use zap\http\Session;
use zap\http\ZapRequest;

const Z_DAY = 86400;
const Z_HOUR = 3600;

const Z_DATE_TIME = 'Y-m-d H:i:s';
const Z_DATE = 'Y-m-d';
const Z_TIME = 'H:i:s';

const FLASH_INFO = 'info';
const FLASH_WARNING = 'warning';
const FLASH_ERROR = 'error';
const FLASH_SUCCESS = 'success';

const ASSETS_HEAD = 'assets_head_urls';
const ASSETS_HEAD_TEXT = 'assets_head_text';
const ASSETS_BODY = 'assets_body_urls';
const ASSETS_BODY_TEXT = 'assets_body_text';


const FETCH_LAZY = 1;
const FETCH_ASSOC = 2;
const FETCH_NUM = 3;
const FETCH_BOTH = 4;
const FETCH_OBJ = 5;
const FETCH_BOUND = 6;
const FETCH_COLUMN = 7;
const FETCH_CLASS = 8;
const FETCH_INTO = 9;
const FETCH_FUNC = 10;
const FETCH_GROUP = 65536;
const FETCH_UNIQUE = 196608;
const FETCH_KEY_PAIR = 12;

function app(): \zap\App
{
    return \zap\App::instance();
}

function base_url($url = null): string
{
    return app()->baseUrl($url);
}

function view($template,$data,$return = false): ?string
{
    return \zap\view\View::render($template,$data,$return);
}

/**
 * Site Root Path
 * @param $path
 *
 * @return string
 */
function root_path($path = null): string
{
    return \zap\App::instance()->rootPath($path);
}

function base_path($path = null){
    return \zap\App::instance()->basePath($path);
}

function config_path($filename = null): string
{
    return \zap\App::instance()->configPath($filename);
}

function storage_path($filename = null): string
{
    return \zap\App::instance()->storagePath($filename);
}

function resource_path($filename = null): string
{
    return \zap\App::instance()->resourcesPath($filename);
}

function assets_path($filename = null): string
{
    return \zap\App::instance()->assetsPath($filename);
}

function themes_path($filename = null): string
{
    return \zap\App::instance()->themesPath($filename);
}

function var_path($filename = null): string
{
    return \zap\App::instance()->varPath($filename);
}

/**
 * 访问配置文件
 * @param $name
 * @param $default
 *
 * @return array|mixed|null
 */
function config($name,$default = null){
    return \zap\Config::get($name,$default);
}

function config_set($name,$value){
    \zap\Config::instance()->set($name,$value);
}

function config_has($name){
    return \zap\Config::instance()->has($name);
}

function _e($html) {
    return htmlentities($html, ENT_QUOTES, 'UTF-8');
}


function object_get($object, $key, $default = null) {
    if (is_null($key) || trim($key) == '') {
        return $object;
    }
    foreach (explode('.', $key) as $segment) {
        if (!is_object($object) || !isset($object->{$segment})) {
            return $default;
        }
        $object = $object->{$segment};
    }
    return $object;
}

function arr_get(&$array, $key, $default = null,$type = null) {
    if($type){
        return $type(\zap\util\Arr::get($array,$key,$default));
    }
    return \zap\util\Arr::get($array,$key,$default);
}

function arr_has(&$array, $key) {
    return \zap\util\Arr::has($array,$key);
}

function arr_set(&$array, $key,$value) {
    return \zap\util\Arr::set($array,$key,$value);
}

/**
 * array to object
 * @param array $array
 * @return \stdClass
 */
function arrayToObject($array) {
    if (!is_array($array)) {
        return $array;
    }

    $object = new \stdClass();
    foreach ($array as $name => $value) {
        $name = strtolower(trim($name));
        if (!empty($name)) {
            $object->$name = arrayToObject($value);
        }
    }
    return $object;
}

function zap_pp() {
    $params = func_get_args();
    echo '<pre>';
    foreach ($params as $value) {
        print_r($value);
    }
    echo '</pre>';
    exit;
}

function base64_url_encode($data)
{
    $base64Url = strtr(base64_encode($data), '+/', '-_');

    return rtrim($base64Url, '=');
}

function base64_url_decode($base64Url)
{
    return base64_decode(strtr($base64Url, '-_', '+/'));
}

/**
 * Log
 *
 * @param string $name default app name
 *
 * @return \Monolog\Logger
 * @throws \Exception
 */
function logger($name = 'app')
{
    return app()->getLogger($name);
}

function trans($key,$params=null,$value=null){
    return \zap\i18n\Language::trans($key,$params,$value);
}


function is_assoc($array) {
    return is_array($array) and (array_values($array) !== $array);
}

function utf8_uri_encode( $utf8_string, $length = 0 ) {
    $unicode = '';
    $values = array();
    $num_octets = 1;
    $unicode_length = 0;

    $string_length = strlen( $utf8_string );
    for ($i = 0; $i < $string_length; $i++ ) {

        $value = ord( $utf8_string[ $i ] );

        if ( $value < 128 ) {
            if ( $length && ( $unicode_length >= $length ) )
                break;
            $unicode .= chr($value);
            $unicode_length++;
        } else {
            if ( count( $values ) == 0 ) $num_octets = ( $value < 224 ) ? 2 : 3;

            $values[] = $value;

            if ( $length && ( $unicode_length + ($num_octets * 3) ) > $length )
                break;
            if ( count( $values ) == $num_octets ) {
                if ($num_octets == 3) {
                    $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]) . '%' . dechex($values[2]);
                    $unicode_length += 9;
                } else {
                    $unicode .= '%' . dechex($values[0]) . '%' . dechex($values[1]);
                    $unicode_length += 6;
                }

                $values = array();
                $num_octets = 1;
            }
        }
    }

    return $unicode;
}
function seems_utf8($str) {
    $length = strlen($str);
    for ($i=0; $i < $length; $i++) {
        $c = ord($str[$i]);
        if ($c < 0x80) $n = 0; # 0bbbbbbb
        elseif (($c & 0xE0) == 0xC0) $n=1; # 110bbbbb
        elseif (($c & 0xF0) == 0xE0) $n=2; # 1110bbbb
        elseif (($c & 0xF8) == 0xF0) $n=3; # 11110bbb
        elseif (($c & 0xFC) == 0xF8) $n=4; # 111110bb
        elseif (($c & 0xFE) == 0xFC) $n=5; # 1111110b
        else return false; # Does not match any model
        for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
            if ((++$i == $length) || ((ord($str[$i]) & 0xC0) != 0x80))
                return false;
        }
    }
    return true;
}
function sanitize($title): string
{
    $title = strip_tags($title);
    // Preserve escaped octets.
    $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
    // Remove percent signs that are not part of an octet.
    $title = str_replace('%', '', $title);
    // Restore octets.
    $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

    if (seems_utf8($title)) {
        if (function_exists('mb_strtolower')) {
            $title = mb_strtolower($title, 'UTF-8');
        }
        $title = utf8_uri_encode($title, 200);
    }

    $title = strtolower($title);
    $title = preg_replace('/&.+?;/', '', $title); // kill entities
    $title = str_replace('.', '-', $title);
    $title = preg_replace('/[^%a-z0-9 _-]/', '', $title);
    $title = preg_replace('/\s+/', '-', $title);
    $title = preg_replace('|-+|', '-', $title);
    $title = trim($title, '-');

    return $title;
}


function url_to($url, $params = null, $queryString = true) {
    return \zap\facades\Url::to($url,$params,$queryString);
}

function url_action($controller,$queryParams = null,$pathParams = null){
    return \zap\facades\Url::action($controller,$queryParams,$pathParams);
}

function register_scripts($urls, $position = ASSETS_HEAD) {
    $position = 'scripts_' . $position;
    if (!app()->$position) {
        app()->$position = new ArrayObject([],ArrayObject::ARRAY_AS_PROPS);
    }
    if(is_array($urls)){
        foreach ($urls as $url){
            app()->$position[] = $url;
        }
    }else{
        app()->$position[] = $urls;
    }



}

function register_styles($urls, $position = ASSETS_HEAD) {
    $position = 'styles_' . $position;
    if (!app()->$position) {
        app()->$position = new ArrayObject([],ArrayObject::ARRAY_AS_PROPS);
    }
    if(is_array($urls)){
        foreach ($urls as $url){
            app()->$position[] = $url;
        }
    }else{
        app()->$position[] = $urls;
    }
}

function print_scripts($position = ASSETS_HEAD) {
    $key = 'scripts_' . $position;
    if (!app()->has($key)) {
        return false;
    }
    if ($position == ASSETS_HEAD) {
        foreach (app()->$key as $script) {
            echo '<script src="', $script, '"></script>', "\n";
        }
    } else if ($position == ASSETS_HEAD_TEXT) {
        //body
        echo '<script type="text/javascript">', "\n";
        echo '//<![CDATA[', "\n";
        foreach (app()->$key as $script) {
            echo $script, "\n";
        }
        echo '//]]>', "\n";
        echo '</script>', "\n";
    } else if ($position == ASSETS_BODY) {
        foreach (app()->$key as $script) {
            echo '<script src="', $script, '"></script>', "\n";
        }
    } else if ($position == ASSETS_BODY_TEXT) {
        echo '<script type="text/javascript">', "\n";
        echo '//<![CDATA[', "\n";
        foreach (app()->$key as $script) {
            echo $script, "\n";
        }
        echo '//]]>', "\n";
        echo '</script>', "\n";
    }
}

function print_styles() {
    $pos = 'styles_' . ASSETS_HEAD_TEXT;

    if (app()->has('styles_' . ASSETS_HEAD)) {
        foreach (app()->get('styles_' . ASSETS_HEAD) as $style) {
            echo '<link rel="stylesheet" href="', $style, '">',"\n";
        }
    }
    if (app()->has('styles_' . ASSETS_HEAD_TEXT)) {
        //body

        echo '<style>', "\n";
        foreach (app()->get('styles_' . ASSETS_HEAD_TEXT) as $style) {
            echo $style, "\n";
        }
        echo '</style>', "\n";
    }
}


function session(): Session
{
    return Session::instance();
}

function set_session($name, $value) {
    Session::instance()->set($name, $value);
}

function get_session($name, $default = null) {
    return Session::instance()->get($name, $default);
}

function has_session($name): bool
{
    return Session::instance()->has($name);
}

function remove_session($name) {
    Session::instance()->remove($name);
}

function add_flash($message,$type = FLASH_INFO): Session
{
    return Session::instance()->add_flash($message,$type);
}

/**
 * @param $type
 * @param $first bool true 返回第一个消息
 *
 * @return array|false|mixed
 */
function get_flash($type = null, bool $first = false){
    if(!is_null($type) && $first){
        return current(Session::instance()->getFlash($type));
    }
    return Session::instance()->getFlash($type);
}

function has_flash($type = null): bool
{
    return Session::instance()->hasFlash($type);
}

function clear_flash($type = null){
    return Session::instance()->clearFlash($type);
}

function req(): ZapRequest
{
    return ZapRequest::instance();
}

function response($content = null, int $statusCode = 200, ?array $headers = []): Response
{
    return new Response($content, $statusCode, $headers);
}


// hooks
function add_filter($hookName,$callback, int $priority = 10){
    \zap\component\Hooks::instance()->add_filter($hookName,$callback, $priority);
}

function add_action($hookName,$callback, int $priority = 10){
    \zap\component\Hooks::instance()->add_action($hookName,$callback, $priority);
}

function apply_filters($hookName,$value,...$args){
    \zap\component\Hooks::instance()->apply_filters($hookName,$value,...$args);
}

function do_action($hookName,...$args){
    \zap\component\Hooks::instance()->do_action($hookName,...$args);
}

function remove_filter($hookName,$callback,$priority = 10){
    \zap\component\Hooks::instance()->remove_filter($hookName,$callback,$priority);
}

function remove_action($hookName,$callback,$priority = 10){
    \zap\component\Hooks::instance()->remove_action($hookName,$callback,$priority);
}

function remove_all_filter($hookName){
    \zap\component\Hooks::instance()->remove_all_filter($hookName);
}

function remove_all_action($hookName){
    \zap\component\Hooks::instance()->remove_all_action($hookName);
}