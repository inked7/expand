<?php

namespace Inked7\Expand\Traits;

trait DateTime
{
    /**
     * 返回今日开始和结束的时间戳
     *
     * @return array
     */
    public static function today()
    {
        return [
            mktime(0, 0, 0, date('m'), date('d'), date('Y')),
            mktime(23, 59, 59, date('m'), date('d'), date('Y'))
        ];
    }

    /**
     * 返回昨日开始和结束的时间戳
     *
     * @return array
     */
    public static function yesterday($type = '')
    {


        if ($type == 1) {
            $yesterday = date('d') - 2;
        } else {
            $yesterday = date('d') - 1;
        }
        return [
            mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
            mktime(23, 59, 59, date('m'), $yesterday, date('Y'))
        ];
    }

    /**
     * 返回30天开始和结束的时间戳
     *
     * @return array
     */
    public static function recent30()
    {
        $yesterday = date('d') - 30;
        $date = date('d');
        return [
            mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
            mktime(23, 59, 59, date('m'), $date, date('Y'))
        ];
    }

    /**
     * 返回60天开始和结束的时间戳
     *
     * @return array
     */
    public static function recent60()
    {
        $yesterday = date('d') - 60;
        $date = date('d');
        return [
            mktime(0, 0, 0, date('m'), $yesterday, date('Y')),
            mktime(23, 59, 59, date('m'), $date, date('Y'))
        ];
    }

    /**
     * 返回本周开始和结束的时间戳
     *
     * @return array
     */
    public static function week()
    {
        list($y, $m, $d, $w) = explode('-', date('Y-m-d-w'));
        if ($w == 0) $w = 7; //修正周日的问题
        return [
            mktime(0, 0, 0, $m, $d - $w + 1, $y), mktime(23, 59, 59, $m, $d - $w + 7, $y)
        ];
    }

    /**
     * 返回上周开始和结束的时间戳
     *
     * @return array
     */
    public static function lastWeek($type = '')
    {
        $timestamp = time();
        if ($type == 1) {
            $last_start = strtotime(date('Y-m-d', strtotime("last week Monday", $timestamp)));  //上周开始日期
            $last_end = strtotime(date('Y-m-d', strtotime("last week Sunday", $timestamp))) + 24 * 3600 - 1;  //上周结束日期
            return [
                strtotime(date('Y-m-d', strtotime("last week Monday", $last_start))),
                strtotime(date('Y-m-d', strtotime("last week Sunday", $last_end))) + 24 * 3600 - 1
            ];
        }
        return [
            strtotime(date('Y-m-d', strtotime("last week Monday", $timestamp))),
            strtotime(date('Y-m-d', strtotime("last week Sunday", $timestamp))) + 24 * 3600 - 1
        ];
    }

    /**
     * 返回本月开始和结束的时间戳
     *
     * @return array
     */
    public static function month($everyDay = false)
    {
        return [
            mktime(0, 0, 0, date('m'), 1, date('Y')),
            mktime(23, 59, 59, date('m'), date('t'), date('Y'))
        ];
    }

    /**
     * 返回上个月开始和结束的时间戳
     *
     * @return array
     */
    public static function lastMonth($type = '')
    {
        if ($type == 1) {

            $begin = mktime(0, 0, 0, date('m') - 2, 1, date('Y'));
            $end = mktime(23, 59, 59, date('m') - 2, date('t', $begin), date('Y'));
            return [$begin, $end];
        }

        $begin = mktime(0, 0, 0, date('m') - 1, 1, date('Y'));
        $end = mktime(23, 59, 59, date('m') - 1, date('t', $begin), date('Y'));
        return [$begin, $end];
    }

    /**
     * 返回今年开始和结束的时间戳
     *
     * @return array
     */
    public static function year()
    {
        return [
            mktime(0, 0, 0, 1, 1, date('Y')),
            mktime(23, 59, 59, 12, 31, date('Y'))
        ];
    }

    /**
     * 返回去年开始和结束的时间戳
     *
     * @return array
     */
    public static function lastYear($type = '')
    {
        if ($type == 1) {
            $year = date('Y') - 2;
            return [
                mktime(0, 0, 0, 1, 1, $year),
                mktime(23, 59, 59, 12, 31, $year)
            ];
        }
        $year = date('Y') - 1;
        return [
            mktime(0, 0, 0, 1, 1, $year),
            mktime(23, 59, 59, 12, 31, $year)
        ];
    }

    public static function dayOf()
    {
    }

    /**
     * 获取几天前零点到现在/昨日结束的时间戳
     *
     * @param int $day 天数
     * @param bool $now 返回现在或者昨天结束时间戳
     * @return array
     */
    public static function dayToNow($day = 1, $now = true)
    {
        $end = time();
        if (!$now) {
            list($foo, $end) = self::yesterday();
        }

        return [
            mktime(0, 0, 0, date('m'), date('d') - $day, date('Y')),
            $end
        ];
    }

    /**
     * 返回几天前的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAgo($day = 1)
    {
        $nowTime = time();
        return $nowTime - self::daysToSecond($day);
    }

    /**
     * 返回几天后的时间戳
     *
     * @param int $day
     * @return int
     */
    public static function daysAfter($day = 1)
    {
        $nowTime = time();
        return $nowTime + self::daysToSecond($day);
    }

    /**
     * 天数转换成秒数
     *
     * @param int $day
     * @return int
     */
    public static function daysToSecond($day = 1)
    {
        return $day * 86400;
    }

    /**
     * 周数转换成秒数
     *
     * @param int $week
     * @return int
     */
    public static function weekToSecond($week = 1)
    {
        return self::daysToSecond() * 7 * $week;
    }

    public static function startTimeToEndTime()
    {
    }

    /**
     * 计算截止日期剩余时间
     *
     * @param  null|string $endTimeString
     * @return null|int
     */
    public static function remainTime(?string $endTimeString)
    {
        if (!$endTimeString) {
            return null;
        }

        $endTime = strtotime($endTimeString);
        $now = time();

        $remainTime = 0;
        if ($endTime > time()) {
            $remainTime = $endTime - $now;
        }

        return $remainTime;
    }

    /**
     * 剩余时间秒数转换为人性化时间
     *
     * @param  null|string|integer $endTimeOrRemainSeconds
     * @return null|string
     */
    public static function secondsToTime(mixed $endTimeOrRemainSeconds)
    {
        if (is_null($endTimeOrRemainSeconds)) {
            return null;;
        }

        if (is_string($endTimeOrRemainSeconds)) {
            $seconds = static::remainTime($endTimeOrRemainSeconds);
        } else if (is_int($endTimeOrRemainSeconds)) {
            $seconds = $endTimeOrRemainSeconds;
        }

        $years = floor($seconds / 31536000);
        $months = floor(($seconds - ($years * 31536000)) / 2592000);
        $days = floor(($seconds - ($years * 31536000) - ($months * 2592000)) / 86400);
        $hours = floor(($seconds - ($years * 31536000) - ($months * 2592000) - ($days * 86400)) / 3600);
        $minutes = floor(($seconds - ($years * 31536000) - ($months * 2592000) - ($days * 86400) - ($hours * 3600)) / 60);
        $seconds = ($seconds - ($years * 31536000) - ($months * 2592000) - ($days * 86400) - ($hours * 3600) - ($minutes * 60));

        $timeString = "";

        if($years > 0) {
            $timeString .= $years . "年";
        }

        if($months > 0) {
            $timeString .= $months . "个月";
        }

        if($days > 0) {
            $timeString .= $days . "天";
        }

        if($hours > 0) {
            $timeString .= $hours . "小时";
        }

        if($minutes > 0) {
            $timeString .= $minutes . "分钟";
        }

        if($seconds > 0) {
            $timeString .= $seconds . "秒";
        }

        return $timeString;
    }
}
