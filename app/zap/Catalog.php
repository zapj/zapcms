<?php

namespace zap;

use zap\traits\SingletonTrait;

class Catalog extends Category
{

    use SingletonTrait;

    const POSITION_TOP = 1;
    const POSITION_LEFT = 2;
    const POSITION_RIGHT = 4;
    const POSITION_BOTTOM = 8;
    const POSITION_CENTER = 16;
    const POSITION_NONE = 32;

    const POSITION_ALL = self::POSITION_TOP | self::POSITION_LEFT | self::POSITION_RIGHT | self::POSITION_BOTTOM | self::POSITION_CENTER;

    protected static $POSITION_LIST  = [self::POSITION_ALL => '全部',
        1 => '顶部导航',
        2 => '左侧导航',
        4 => '右侧导航',
        8 => '底部导航',
        16 => '中间导航',
        self::POSITION_NONE=>'不显示'
    ];
    public static function getPositions(): array
    {
        return static::$POSITION_LIST;
    }

    public static function positionTitle($position){
        return static::$POSITION_LIST[$position];
    }

    public function __construct()
    {
        parent::__construct('catalog');
    }

    public function add($data, $pid)
    {
        $data['created_at'] = time();
        return parent::add($data, $pid);
    }

    public function getCatalogPathById($catalogId){
        $catalog = $this->get($catalogId);
//        $rootId = explode(',',$catalog['path'])[0] ?? 0;
        $ids = array_filter(explode(',',$catalog['path']));
        if(empty($ids)){
            return [];
        }
        return $this->getAll([
            [$this->primaryKey,'IN',$ids]
        ]);
    }

}
