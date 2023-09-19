<?php

namespace zap\util;

class Str {

    public static function format($string,$params,$value = null){
        if(is_array($params)){
            $search = array_map(function($key){ return '{'.$key.'}'; }, array_keys($params));
            $replace = array_values($params);
            return str_replace($search,$replace,$string);
        }
        return str_replace('{'.$params.'}',$value,$string);
    }

    public static function token() {
        return md5(str_shuffle(chr(mt_rand(32, 126)) . uniqid() . microtime(TRUE)));
    }

    public static function len($str, $encoding = 'UTF-8') {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, $encoding);
        }
        return strlen($str);
    }

    public static function startsWith($string, $start, $caseSensitive = true) {
        if ($caseSensitive == false) {
            $string = strtolower($string);
        }
        return $start === "" || strrpos($string, $start, -strlen($string)) !== FALSE;
    }

    public static function endsWith($string, $end, $caseSensitive = true) {
        if ($caseSensitive == false) {
            $string = strtolower($string);
        }
        return $end === "" || (($temp = strlen($string) - strlen($end)) >= 0 && strpos($string, $end, $temp) !== FALSE);
    }

    public static function startsWithChar($needle, $haystack) {
        return ($needle[0] === $haystack);
    }

    public static function endsWithChar($needle, $haystack) {
        return (substr($needle, -1) === $haystack);
    }

    /**
     * FormatType
     */
    public static function isJson($str) {
        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function isHtml($str) {
        return strlen(strip_tags($str)) < strlen($str);
    }

    public static function replaceArray($search, array $replace, $subject) {
        foreach ($replace as $value) {
            $subject = preg_replace('/' . $search . '/', $value, $subject, 1);
        }
        return $subject;
    }

    public static function contains($haystack, $needles) {
        if (is_string($needles)) {
            return strpos($haystack, $needles);
        }
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }

    public static function containsArray($haystack, $needles) {
        if (is_string($needles)) {
            return strpos($haystack, $needles);
        }
        $all_needle = true;
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) !== false && $all_needle) {
                $all_needle = true;
            } else {
                $all_needle = false;
            }
        }
        return $all_needle;
    }

    public static function truncate($value, $limit = 100, $end = '...') {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }
        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }

    public static function substr($string, $start, $length = null) {
        if(function_exists('mb_substr')){
            return mb_substr($string, $start, $length, 'UTF-8');
        }
        return substr($string, $start, $length);
    }

    public static function slug($title, $separator = '-') {

        $flip = $separator == '-' ? '_' : '-';
        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);
        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title));
        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);
        return trim($title, $separator);
    }

    /**
     * 判断是否是ASCII
     * @param type $string
     * @return type
     */
    public static function isAscii($string) {
        return !preg_match('/[^\x00-\x7F]/S', $string);
    }

    /**
     * 验证手机号码
     */
    public static function isMobile($str) {
        if (empty($str)) {
            return false;
        }
        return preg_match('#^13[\d]{9}$|14^[0-9]\d{8}|^15[0-9]\d{8}$|^16[0-9]\d{8}$|^19[0-9]\d{8}$|^18[0-9]\d{8}$#', $str);
    }

    /**
     * 验证固定电话
     */
    public static function isTel($str) {
        if (empty($str)) {
            return true;
        }
        return preg_match('/^((\(\d{2,3}\))|(\d{3}\-))?(\(0\d{2,3}\)|0\d{2,3}-)?[1-9]\d{6,7}(\-\d{1,4})?$/', trim($str));
    }

    /**
     * 验证qq号码
     */
    public static function isQQ($str) {
        if (empty($str)) {
            return false;
        }

        return preg_match('/^[1-9]\d{4,12}$/', trim($str));
    }

    /**
     * 验证邮政编码
     */
    public static function isZipCode($str) {
        if (empty($str)) {
            return true;
        }

        return preg_match('/^[1-9]\d{5}$/', trim($str));
    }

    /**
     * 验证ip
     */
    public static function isIP($value,$flag = null) {
        if($flag){
            return filter_var($value, FILTER_VALIDATE_IP,$flag) !== false;
        }
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * 验证身份证(中国)
     */
    public static function idCard($str) {
        $str = trim($str);
        if (empty($str)) {
            return false;
        }

        if (preg_match("/^([0-9]{15}|[0-9]{17}[0-9a-z])$/i", $str)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 验证网址
     */
    public static function isURL($str) {
        if (empty($str)) {
            return false;
        }

        return preg_match('#(http|https|ftp|ftps)://([\w-]+\.)+[\w-]+(/[\w-./?%&=]*)?#i', $str) ? false : true;
    }

    /**
     * 验证邮箱
     */
    public static function isEmail($str) {
        if (empty($str)) {
            return false;
        }
        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if (strpos($str, '@') !== false && strpos($str, '.') !== false) {
            if (preg_match($chars, $str)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}