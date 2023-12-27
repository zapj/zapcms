<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:29
 * @lastModified 2023/12/27 上午11:10
 *
 */

namespace zap\cms;

use zap\traits\SingletonTrait;

class AdminMenu extends Category
{
    use SingletonTrait;

    const POSITION_TOP = 1;
    const POSITION_LEFT = 2;
    const POSITION_RIGHT = 3;

    protected static array $POSITION_LIST  = [
        1 => '顶部导航',
        2 => '左侧导航',
        3 => '右侧导航'
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

    public function add($data)
    {
        $data['updated_at'] = time();
        $data['created_at'] = time();
        return parent::add($data);
    }

    public function update($data, $id)
    {
        $data['updated_at'] = time();
        return parent::update($data, $id);
    }


}