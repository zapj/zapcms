<?php

namespace zap\util;

class Fmt
{
    /**
     * 格式化单位
     */
    static public function ByteToHuman($size, $dec = 2): string
    {
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
     * @param int $fen 分
     * @param string $thousands_sep 分隔符, 默认 ( , )
     * @return string
     */
    public static function FenToYuan(int $fen = 0, string $thousands_sep = ","): string
    {
        return number_format($fen / 100, 2, '.', $thousands_sep);
    }

    /**
     * 元转分
     * @param float $yuan
     * @return int 返回分
     */
    public static function YuanToFen($yuan = 0): int
    {
        return intval(round(number_format($yuan, 2, '.', '') * 100));
    }

}