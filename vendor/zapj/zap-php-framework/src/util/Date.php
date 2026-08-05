<?php

namespace zap\util;

class Date
{
    protected ?\DateTimeZone $timezone = null;

    // ========== 配置 ==========

    public function setTimeZone(string $timezone = 'UTC'): void
    {
        $this->timezone = new \DateTimeZone($timezone);
    }

    /**
     * 获取当前时区
     */
    public function getTimeZone(): ?\DateTimeZone
    {
        return $this->timezone;
    }

    /**
     * 解析时区参数
     */
    protected function resolveTimezone(?string $timezone = null): ?\DateTimeZone
    {
        if ($timezone !== null) {
            return new \DateTimeZone($timezone);
        }
        return $this->timezone;
    }

    // ========== 实例化 ==========

    /**
     * @throws \Exception
     */
    public function create(string $datetime, ?string $timezone = null): \DateTime
    {
        return new \DateTime($datetime, $this->resolveTimezone($timezone));
    }

    /**
     * @throws \Exception
     */
    public function now(?string $timezone = null): \DateTime
    {
        return new \DateTime('now', $this->resolveTimezone($timezone));
    }

    // ========== 格式化 ==========

    public function format(string $format, string $datetime, ?string $timezone = null): string
    {
        $date = new \DateTime($datetime, $this->resolveTimezone($timezone));
        return $date->format($format);
    }

    // ========== 差值 ==========

    /**
     * @throws \Exception
     */
    public function diff(string $datetime1, string $datetime2): \DateInterval
    {
        $date1 = new \DateTime($datetime1, $this->timezone);
        $date2 = new \DateTime($datetime2, $this->timezone);
        return $date1->diff($date2);
    }

    // ========== 静态工厂（便捷方法） ==========

    /**
     * 创建 DateTime 实例
     */
    public static function make(string $datetime = 'now'): \DateTime
    {
        return new \DateTime($datetime);
    }

    /**
     * 创建指定时区的 DateTime 实例
     */
    public static function makeTz(string $datetime, string $timezone): \DateTime
    {
        return new \DateTime($datetime, new \DateTimeZone($timezone));
    }

    /**
     * 静态格式化
     */
    public static function formatStatic(string $format, string $datetime = 'now'): string
    {
        return (new \DateTime($datetime))->format($format);
    }

    /**
     * 获取当前时间常用格式
     */
    public static function nowDate(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }

    /**
     * 时间戳转日期
     */
    public static function fromTimestamp(int $timestamp, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, $timestamp);
    }

    /**
     * 计算距今时间（如 "3分钟前"）
     */
    public static function ago($datetime): string
    {
        if (is_numeric($datetime)) {
            $timestamp = (int) $datetime;
        } else {
            try {
                $timestamp = (new \DateTime($datetime))->getTimestamp();
            } catch (\Exception $e) {
                return '';
            }
        }

        $diff = time() - $timestamp;

        if ($diff < 0) {
            return '刚刚';
        }
        if ($diff < 60) {
            return $diff . '秒前';
        }
        if ($diff < 3600) {
            return floor($diff / 60) . '分钟前';
        }
        if ($diff < 86400) {
            return floor($diff / 3600) . '小时前';
        }
        if ($diff < 2592000) {
            return floor($diff / 86400) . '天前';
        }
        if ($diff < 31536000) {
            return floor($diff / 2592000) . '个月前';
        }
        return floor($diff / 31536000) . '年前';
    }

    /**
     * 判断是否为今天
     */
    public static function isToday(string $datetime): bool
    {
        return (new \DateTime($datetime))->format('Y-m-d') === date('Y-m-d');
    }
}
