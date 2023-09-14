<?php

namespace zap;

use zap\Category;

class AdminMenu extends Category
{

    const POSITION_TOP = 1;
    const POSITION_LEFT = 2;
    const POSITION_RIGHT = 4;

    const POSITION_ALL = self::POSITION_TOP | self::POSITION_LEFT | self::POSITION_RIGHT;

    protected static $POSITION_LIST  = [
        0=>'不显示',
        1 => '顶部导航',
        2 => '左侧导航',
        4 => '右侧导航',
        self::POSITION_ALL => '全部',
    ];
    public static function getPositions(): array
    {
        return static::$POSITION_LIST;
    }

    public static function positionTitle($position): string
    {
        return static::$POSITION_LIST[$position];
    }

    public function __construct()
    {
        parent::__construct('admin_menu');
    }

    public function add($data, $pid)
    {
        $data['updated_at'] = time();
        $data['created_at'] = time();
        return parent::add($data, $pid);
    }


}