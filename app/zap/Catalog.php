<?php

namespace zap;

use zap\traits\SingletonTrait;

class Catalog extends Category
{

    use SingletonTrait;

    const POSITION_TOP = 1;
    const POSITION_LEFT = 2;
    const POSITION_RIGHT = 3;
    const POSITION_BOTTOM = 4;


    const POSITION_ALL = "1,2,3,4";

    protected static array $POSITION_LIST  = [
        1 => '顶部导航',
        2 => '左侧导航',
        3 => '右侧导航',
        4 => '底部导航',
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
