<?php

namespace zap\cms\models;

use zap\DB;

class AdminLog
{
    /**
     * 记录一条活动日志
     */
    public static function log(string $title, string $content = '', ?int $uid = null, ?string $username = null): void
    {
        $uid = $uid ?? (int)(\zap\cms\Auth::user()['id'] ?? 0);
        $username = $username ?? (\zap\cms\Auth::user()['username'] ?? 'system');

        DB::table('admin_logs')->insert([
            'uid'          => $uid,
            'username'     => $username,
            'title'        => $title,
            'content'      => $content,
            'ipaddress'    => \zap\http\Request::ip(),
            'request_url'  => \zap\http\Request::url(),
            'user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'request_time' => time(),
        ]);
    }

    /**
     * 获取指定用户的活动日志（分页）
     */
    public static function getByUser(int $uid, int $limit = 50, int $offset = 0): array
    {
        return DB::table('admin_logs')
            ->where('uid', $uid)
            ->orderBy('request_time', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->fetchAll(FETCH_ASSOC);
    }

    /**
     * 获取活动日志总数
     */
    public static function countByUser(int $uid): int
    {
        $result = DB::table('admin_logs')
            ->select('COUNT(*) AS total')
            ->where('uid', $uid)
            ->fetch(FETCH_ASSOC);
        return (int)($result['total'] ?? 0);
    }
}
