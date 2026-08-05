<?php

namespace zap\util;

class Fmt
{
    /**
     * 字节数转人类可读格式
     */
    public static function ByteToHuman(int $size, int $dec = 2): string
    {
        $units = ["B", "KB", "MB", "GB", "TB", "PB"];
        $pos = 0;
        $absSize = abs($size);
        while ($absSize >= 1024 && $pos < count($units) - 1) {
            $absSize /= 1024;
            $pos++;
        }
        $sign = $size < 0 ? '-' : '';
        return $sign . round($absSize, $dec) . " " . $units[$pos];
    }

    /**
     * 分转元
     * @param int $fen 分（支持负数）
     * @param string $thousandsSep 千分位分隔符
     */
    public static function FenToYuan(int $fen = 0, string $thousandsSep = ","): string
    {
        return number_format($fen / 100, 2, '.', $thousandsSep);
    }

    /**
     * 元转分
     */
    public static function YuanToFen(float $yuan = 0): int
    {
        return (int) round(number_format($yuan, 2, '.', '') * 100);
    }

    /**
     * 格式化时间间隔（秒数转为可读格式）
     */
    public static function duration(int $seconds): string
    {
        if ($seconds < 0) {
            return '0秒';
        }
        $units = [
            86400 => '天',
            3600  => '小时',
            60    => '分钟',
            1     => '秒',
        ];
        $parts = [];
        foreach ($units as $divisor => $label) {
            if ($seconds >= $divisor) {
                $parts[] = intdiv($seconds, $divisor) . $label;
                $seconds %= $divisor;
            }
        }
        return empty($parts) ? '0秒' : implode('', $parts);
    }

    /**
     * 数字格式化为千分位
     */
    public static function number(float $number, int $decimals = 0): string
    {
        return number_format($number, $decimals, '.', ',');
    }

    /**
     * 格式化百分比
     */
    public static function percent(float $number, int $decimals = 2): string
    {
        return number_format($number * 100, $decimals) . '%';
    }
}
