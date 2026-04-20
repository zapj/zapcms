<?php

namespace zap\view;

use Exception;

class ZView
{
    public static function render($path, $data = array(), $return = false) {
        if (!is_file($path)) {
            return '';
        }
        $obLevel = ob_get_level();
        $error_level = error_reporting();
        error_reporting(0);
        ob_start();
        extract($data, EXTR_SKIP);
        try {
            include $path;
        } catch (Exception $e) {
            while (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
        }
        error_reporting($error_level);
        if ($return) {
            return ltrim(ob_get_clean());
        }
        echo ltrim(ob_get_clean());
    }
}