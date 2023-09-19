<?php

namespace zap\util;

class Fmt
{
    /**
     * 格式化单位
     */
    static public function ByteToHuman($size, $dec = 2) {
        $a = array("B", "KB", "MB", "GB", "TB", "PB");
        $pos = 0;
        while ($size >= 1024) {
            $size /= 1024;
            $pos++;
        }
        return round($size, $dec) . " " . $a[$pos];
    }

    /**
     * 分转元
     * @param type $fen
     * @return type
     */
    public static function FenToYuan($fen = 0, $thousands_sep = ",") {
        return number_format($fen / 100, 2, '.', $thousands_sep);
    }

    /**
     * 元转分
     * @param type $yuan
     * @return type
     */
    public static function YuanToFen($yuan = 0) {
        return intval(round(number_format($yuan, 2, '.', '') * 100));
    }

}