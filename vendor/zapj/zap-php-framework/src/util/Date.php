<?php

namespace zap\util;

/**
 * 日期时间工具类
 *
 * 提供 DateTime 实例化、格式化、差值计算、相对时间、日期边界、中文星期等常用操作。
 * 支持实例模式和静态便捷方法两种调用方式，所有方法均支持时区参数。
 *
 * @method static \DateTime       make(string $datetime = 'now', ?string $timezone = null)
 * @method static string          format(string $format, string $datetime = 'now', ?string $timezone = null)
 * @method static string          nowDate(string $format = 'Y-m-d H:i:s', ?string $timezone = null)
 * @method static string          fromTimestamp(int $timestamp, string $format = 'Y-m-d H:i:s', ?string $timezone = null)
 * @method static string          ago(string|int|\DateTimeInterface $datetime)
 * @method static int             diffInSeconds(string|int|\DateTimeInterface $datetime)
 * @method static int             diffInMinutes(string|int|\DateTimeInterface $datetime)
 * @method static int             diffInHours(string|int|\DateTimeInterface $datetime)
 * @method static int             diffInDays(string|int|\DateTimeInterface $datetime)
 * @method static bool            isToday(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isYesterday(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isTomorrow(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isThisWeek(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isThisMonth(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isThisYear(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isPast(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isFuture(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isWeekend(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static bool            isWeekday(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static \DateTime       startOfDay(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static \DateTime       endOfDay(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static \DateTime       startOfWeek(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static \DateTime       endOfWeek(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static \DateTime       startOfMonth(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static \DateTime       endOfMonth(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static \DateTime       startOfYear(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static \DateTime       endOfYear(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static int             daysInMonth(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static string          chineseWeekday(string|int|\DateTimeInterface $datetime, ?string $timezone = null)
 * @method static int             age(string|int|\DateTimeInterface $birthday, ?string $timezone = null)
 * @method static bool            between(string|int|\DateTimeInterface $datetime, string|int|\DateTimeInterface $start, string|int|\DateTimeInterface $end, ?string $timezone = null)
 * @method static \DateTime       addDays(string|int|\DateTimeInterface $datetime, int $days, ?string $timezone = null)
 * @method static \DateTime       subDays(string|int|\DateTimeInterface $datetime, int $days, ?string $timezone = null)
 * @method static \DateTime       addMonths(string|int|\DateTimeInterface $datetime, int $months, ?string $timezone = null)
 * @method static \DateTime       subMonths(string|int|\DateTimeInterface $datetime, int $months, ?string $timezone = null)
 * @method static \DateTime       addYears(string|int|\DateTimeInterface $datetime, int $years, ?string $timezone = null)
 * @method static \DateTime       subYears(string|int|\DateTimeInterface $datetime, int $years, ?string $timezone = null)
 */
class Date
{
    // ────────── 常用格式常量 ──────────

    const FORMAT_DATETIME  = 'Y-m-d H:i:s';
    const FORMAT_DATE      = 'Y-m-d';
    const FORMAT_TIME      = 'H:i:s';
    const FORMAT_SHORT     = 'Y-m-d H:i';
    const FORMAT_CHINESE   = 'Y年m月d日 H:i:s';
    const FORMAT_TIMESTAMP = 'YmdHis';

    /** @var \DateTimeZone|null 当前实例默认时区 */
    protected ?\DateTimeZone $timezone = null;

    // ===================== 配置 =====================

    /**
     * 设置实例默认时区
     *
     * @param string $timezone 时区标识，如 'Asia/Shanghai', 'UTC'
     */
    public function setTimeZone(string $timezone = 'UTC'): self
    {
        $this->timezone = new \DateTimeZone($timezone);
        return $this;
    }

    /**
     * 获取实例默认时区
     */
    public function getTimeZone(): ?\DateTimeZone
    {
        return $this->timezone;
    }

    // ===================== DateTime 实例化 =====================

    /**
     * 创建 DateTime 实例
     *
     * @param string      $datetime 日期时间字符串
     * @param string|null $timezone 时区，null 使用实例默认时区
     * @return \DateTime
     * @throws \Exception
     */
    public function create(string $datetime, ?string $timezone = null): \DateTime
    {
        return new \DateTime($datetime, $this->resolveTimezone($timezone));
    }

    /**
     * 获取当前时间的 DateTime 实例
     *
     * @param string|null $timezone 时区
     * @throws \Exception
     */
    public function now(?string $timezone = null): \DateTime
    {
        return new \DateTime('now', $this->resolveTimezone($timezone));
    }

    // ===================== 格式化 =====================

    /**
     * 格式化日期时间
     *
     * @param string      $format   格式字符串
     * @param string      $datetime 日期时间字符串
     * @param string|null $timezone 时区
     */
    public function format(string $format, string $datetime, ?string $timezone = null): string
    {
        try {
            $date = new \DateTime($datetime, $this->resolveTimezone($timezone));
            return $date->format($format);
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 获取当前时间字符串
     */
    public function nowDate(string $format = self::FORMAT_DATETIME, ?string $timezone = null): string
    {
        try {
            $date = new \DateTime('now', $this->resolveTimezone($timezone));
            return $date->format($format);
        } catch (\Exception $e) {
            return date($format);
        }
    }

    /**
     * 时间戳转日期字符串
     */
    public function fromTimestamp(int $timestamp, string $format = self::FORMAT_DATETIME, ?string $timezone = null): string
    {
        try {
            $date = new \DateTime('@' . $timestamp);
            if ($tz = $this->resolveTimezone($timezone)) {
                $date->setTimezone($tz);
            }
            return $date->format($format);
        } catch (\Exception $e) {
            return date($format, $timestamp);
        }
    }

    // ===================== 相对时间 =====================

    /**
     * 人性化相对时间（如"3分钟前""刚刚""5天后"）
     *
     * @param string|int|\DateTimeInterface $datetime 日期时间字符串、时间戳或 DateTime 对象
     */
    public function ago($datetime): string
    {
        $timestamp = $this->resolveTimestamp($datetime);
        if ($timestamp === null) {
            return '';
        }

        $now  = time();
        $diff = $now - $timestamp;

        if ($diff < 0) {
            $diff = abs($diff);
            if ($diff < 60) {
                return '即将';
            }
            if ($diff < 3600) {
                return floor($diff / 60) . '分钟后';
            }
            if ($diff < 86400) {
                return floor($diff / 3600) . '小时后';
            }
            if ($diff < 2592000) {
                return floor($diff / 86400) . '天后';
            }
            if ($diff < 31536000) {
                return floor($diff / 2592000) . '个月后';
            }
            return floor($diff / 31536000) . '年后';
        }

        if ($diff < 60) {
            return '刚刚';
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
     * 精确相对时间（超过一定阈值时切换为日期格式）
     *
     * @param string|int|\DateTimeInterface $datetime
     * @param string                        $dateFormat 超过阈值后的日期格式
     * @param int                           $dayThreshold 多少天后切换为绝对日期，默认 7 天
     */
    public function relativeTime($datetime, string $dateFormat = self::FORMAT_DATE, int $dayThreshold = 7): string
    {
        $timestamp = $this->resolveTimestamp($datetime);
        if ($timestamp === null) {
            return '';
        }

        $diff = time() - $timestamp;
        $absDiff = abs($diff);
        $prefix  = $diff > 0 ? '前' : '后';
        $suffix  = '';

        if ($absDiff < 60) {
            return $diff > 0 ? '刚刚' : '即将';
        }
        if ($absDiff < 3600) {
            $amount = floor($absDiff / 60);
            $suffix = '分钟' . $prefix;
        } elseif ($absDiff < 86400) {
            $amount = floor($absDiff / 3600);
            $suffix = '小时' . $prefix;
        } elseif ($absDiff < $dayThreshold * 86400) {
            $amount = floor($absDiff / 86400);
            if ($amount === 1 && $diff > 0) {
                return '昨天';
            }
            if ($amount === 1 && $diff < 0) {
                return '明天';
            }
            $suffix = '天' . $prefix;
        } else {
            return date($dateFormat, $timestamp);
        }

        return $amount . $suffix;
    }

    // ===================== 差值计算 =====================

    /**
     * 计算两个日期的差值（返回 \DateInterval）
     *
     * @throws \Exception
     */
    public function diff(string $datetime1, string $datetime2): \DateInterval
    {
        $date1 = new \DateTime($datetime1, $this->timezone);
        $date2 = new \DateTime($datetime2, $this->timezone);
        return $date1->diff($date2);
    }

    /**
     * 从给定日期距今的秒数差
     */
    public function diffInSeconds($datetime): int
    {
        $timestamp = $this->resolveTimestamp($datetime);
        return $timestamp !== null ? time() - $timestamp : 0;
    }

    /**
     * 从给定日期距今的分钟数差
     */
    public function diffInMinutes($datetime): int
    {
        return (int) floor($this->diffInSeconds($datetime) / 60);
    }

    /**
     * 从给定日期距今的小时数差
     */
    public function diffInHours($datetime): int
    {
        return (int) floor($this->diffInSeconds($datetime) / 3600);
    }

    /**
     * 从给定日期距今的天数差
     */
    public function diffInDays($datetime): int
    {
        return (int) floor($this->diffInSeconds($datetime) / 86400);
    }

    // ===================== 日期判断 =====================

    /**
     * 判断是否为今天
     */
    public function isToday($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $now  = new \DateTime('now', $date->getTimezone());
        return $date->format('Y-m-d') === $now->format('Y-m-d');
    }

    /**
     * 判断是否为昨天
     */
    public function isYesterday($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $yesterday = (new \DateTime('yesterday', $date->getTimezone()))->format('Y-m-d');
        return $date->format('Y-m-d') === $yesterday;
    }

    /**
     * 判断是否为明天
     */
    public function isTomorrow($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $tomorrow = (new \DateTime('tomorrow', $date->getTimezone()))->format('Y-m-d');
        return $date->format('Y-m-d') === $tomorrow;
    }

    /**
     * 判断是否在本周内
     */
    public function isThisWeek($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $now  = new \DateTime('now', $date->getTimezone());
        $startOfWeek = (clone $now)->modify('this week')->setTime(0, 0, 0);
        $endOfWeek   = (clone $now)->modify('next week')->setTime(0, 0, 0);
        return $date >= $startOfWeek && $date < $endOfWeek;
    }

    /**
     * 判断是否在本月内
     */
    public function isThisMonth($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $now  = new \DateTime('now', $date->getTimezone());
        return $date->format('Y-m') === $now->format('Y-m');
    }

    /**
     * 判断是否在本年内
     */
    public function isThisYear($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $now  = new \DateTime('now', $date->getTimezone());
        return $date->format('Y') === $now->format('Y');
    }

    /**
     * 判断是否为过去的日期
     */
    public function isPast($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $now  = new \DateTime('now', $date->getTimezone());
        return $date < $now;
    }

    /**
     * 判断是否为未来的日期
     */
    public function isFuture($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $now  = new \DateTime('now', $date->getTimezone());
        return $date > $now;
    }

    /**
     * 判断是否为周末（周六或周日）
     */
    public function isWeekend($datetime, ?string $timezone = null): bool
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $day  = (int)$date->format('N');
        return $day >= 6;
    }

    /**
     * 判断是否为工作日（周一至周五）
     */
    public function isWeekday($datetime, ?string $timezone = null): bool
    {
        return !$this->isWeekend($datetime, $timezone);
    }

    /**
     * 判断给定日期是否在两个日期之间（包含边界）
     */
    public function between($datetime, $start, $end, ?string $timezone = null): bool
    {
        $date  = $this->resolveDateTime($datetime, $timezone);
        $start = $this->resolveDateTime($start, $timezone);
        $end   = $this->resolveDateTime($end, $timezone);
        return $date >= $start && $date <= $end;
    }

    // ===================== 日期边界 =====================

    /**
     * 获取当天开始时间 00:00:00
     */
    public function startOfDay($datetime, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $date->setTime(0, 0, 0);
        return $date;
    }

    /**
     * 获取当天结束时间 23:59:59.999999
     */
    public function endOfDay($datetime, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $date->setTime(23, 59, 59, 999999);
        return $date;
    }

    /**
     * 获取本周一 00:00:00
     */
    public function startOfWeek($datetime, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify('monday this week')->setTime(0, 0, 0);
    }

    /**
     * 获取本周日 23:59:59
     */
    public function endOfWeek($datetime, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify('sunday this week')->setTime(23, 59, 59, 999999);
    }

    /**
     * 获取本月第一天 00:00:00
     */
    public function startOfMonth($datetime, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify('first day of this month')->setTime(0, 0, 0);
    }

    /**
     * 获取本月最后一天 23:59:59
     */
    public function endOfMonth($datetime, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify('last day of this month')->setTime(23, 59, 59, 999999);
    }

    /**
     * 获取本年第一天 00:00:00
     */
    public function startOfYear($datetime, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify('first day of January this year')->setTime(0, 0, 0);
    }

    /**
     * 获取本年最后一天 23:59:59
     */
    public function endOfYear($datetime, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify('last day of December this year')->setTime(23, 59, 59, 999999);
    }

    // ===================== 日期计算 =====================

    /**
     * 获取指定日期所在月份的天数
     */
    public function daysInMonth($datetime, ?string $timezone = null): int
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return (int) $date->format('t');
    }

    /**
     * 增加天数
     */
    public function addDays($datetime, int $days, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify("+{$days} days");
    }

    /**
     * 减少天数
     */
    public function subDays($datetime, int $days, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify("-{$days} days");
    }

    /**
     * 增加月数
     */
    public function addMonths($datetime, int $months, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify("+{$months} months");
    }

    /**
     * 减少月数
     */
    public function subMonths($datetime, int $months, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify("-{$months} months");
    }

    /**
     * 增加年数
     */
    public function addYears($datetime, int $years, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify("+{$years} years");
    }

    /**
     * 减少年数
     */
    public function subYears($datetime, int $years, ?string $timezone = null): \DateTime
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        return $date->modify("-{$years} years");
    }

    // ===================== 本地化 =====================

    /**
     * 获取中文星期名称
     *
     * @param string|int|\DateTimeInterface $datetime
     * @return string 星期一 ~ 星期日
     */
    public function chineseWeekday($datetime, ?string $timezone = null): string
    {
        $date = $this->resolveDateTime($datetime, $timezone);
        $weekdays = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'];
        return $weekdays[(int)$date->format('N') - 1];
    }

    /**
     * 获取中文月份名称
     */
    public function chineseMonth($datetime, ?string $timezone = null): string
    {
        $date    = $this->resolveDateTime($datetime, $timezone);
        $months  = ['一月', '二月', '三月', '四月', '五月', '六月',
                    '七月', '八月', '九月', '十月', '十一月', '十二月'];
        return $months[(int)$date->format('n') - 1];
    }

    /**
     * 计算年龄
     *
     * @param string|int|\DateTimeInterface $birthday 出生日期
     */
    public function age($birthday, ?string $timezone = null): int
    {
        $birthDate = $this->resolveDateTime($birthday, $timezone);
        $now       = new \DateTime('now', $birthDate->getTimezone());

        $age = (int)$birthDate->diff($now)->y;

        // 判断今年生日是否已过
        $currentYearBirthday = (clone $birthDate)->setDate((int)$now->format('Y'), (int)$birthDate->format('m'), (int)$birthDate->format('d'));
        if ($now < $currentYearBirthday) {
            $age--;
        }

        return max(0, $age);
    }

    // ===================== 静态代理 =====================

    /**
     * 通过静态方法代理实例方法调用
     *
     * 用法：Date::ago('2025-01-01') 等价于 (new Date())->ago('2025-01-01')
     *
     * @param string $name      方法名
     * @param array  $arguments 方法参数
     * @return mixed
     * @throws \BadMethodCallException
     */
    public static function __callStatic(string $name, array $arguments)
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }

        if (method_exists($instance, $name)) {
            return $instance->$name(...$arguments);
        }

        throw new \BadMethodCallException("Method Date::{$name}() does not exist.");
    }

    /**
     * 创建 DateTime 实例（兼容旧版静态调用）
     */
    public static function make(string $datetime = 'now'): \DateTime
    {
        return new \DateTime($datetime);
    }

    /**
     * 创建指定时区的 DateTime 实例（兼容旧版静态调用）
     */
    public static function makeTz(string $datetime, string $timezone): \DateTime
    {
        return new \DateTime($datetime, new \DateTimeZone($timezone));
    }

    // ===================== 内部辅助方法 =====================

    /**
     * 解析时区
     */
    protected function resolveTimezone(?string $timezone = null): ?\DateTimeZone
    {
        if ($timezone !== null) {
            return new \DateTimeZone($timezone);
        }
        return $this->timezone;
    }

    /**
     * 统一解析时间戳
     *
     * @param string|int|\DateTimeInterface $datetime
     * @return int|null
     */
    protected function resolveTimestamp($datetime): ?int
    {
        if (is_int($datetime)) {
            return $datetime;
        }

        if (is_numeric($datetime)) {
            return (int) $datetime;
        }

        if ($datetime instanceof \DateTimeInterface) {
            return $datetime->getTimestamp();
        }

        if (is_string($datetime)) {
            $timestamp = strtotime($datetime);
            return $timestamp !== false ? $timestamp : null;
        }

        return null;
    }

    /**
     * 统一解析为 DateTime 对象
     *
     * @param string|int|\DateTimeInterface $datetime
     * @param string|null                   $timezone
     * @return \DateTime
     */
    protected function resolveDateTime($datetime, ?string $timezone = null): \DateTime
    {
        $tz = $this->resolveTimezone($timezone);

        if ($datetime instanceof \DateTime) {
            $clone = clone $datetime;
            if ($tz !== null) {
                $clone->setTimezone($tz);
            }
            return $clone;
        }

        if ($datetime instanceof \DateTimeImmutable) {
            $dt = \DateTime::createFromImmutable($datetime);
            if ($tz !== null) {
                $dt->setTimezone($tz);
            }
            return $dt;
        }

        if (is_int($datetime) || (is_string($datetime) && is_numeric($datetime))) {
            $date = new \DateTime('@' . (int)$datetime);
            if ($tz !== null) {
                $date->setTimezone($tz);
            }
            return $date;
        }

        return new \DateTime((string) $datetime, $tz);
    }
}
