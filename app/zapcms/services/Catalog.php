<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:26
 * @lastModified 2023/12/27 上午11:26
 *
 */

namespace zapcms\services;

use zapcms\models\Node;
use zap\DB;
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

    public function add($data): int
    {
        $node = Node::create([
            'title'=>$data['title'],
            'slug'=>$data['slug'],
            'author_id'=>0,
            'node_type'=>'catalog',
            'mime_type'=>$data['node_type'],
            'status'=>Node::STATUS_PUBLISH,
            'pub_time'=>time(),
            'update_time'=>time(),
            'add_time'=>time(),
        ]);
        if($node->id){
            $data[$this->primaryKey] = $node->id;
        }
        $data['created_at'] = time();
        return parent::add($data);
    }

    public function update($data, $id)
    {
        Node::updateAll([
            'title'=>$data['title'],
            'slug'=>$data['slug'],
//            'node_type'=>$data['node_type'],
            'node_type'=>'catalog',
            'mime_type'=>$data['node_type'],
            'update_time'=>time(),
        ],['id'=>$id]);
        return parent::update($data, $id);
    }

    public function remove($id)
    {
        Node::delete($id);
        DB::table('node_meta')->where('object_id',$id)->delete();
        return parent::remove($id);
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

    public function getSubCatalogList($catalogId){
        // 排除父级自身：path LIKE '5,%' 会匹配到 path='5,'，加 _ 确保至少还有一个后代
        return $this->getAll([
            [$this->pathColumn,'LIKE',"{$catalogId},%_"]
        ]);
    }

    /**
     * 获取指定显示位置的栏目菜单（左侧导航 POSITION_LEFT / 右侧导航 POSITION_RIGHT）
     * 用于模板页面没有相关联的栏目菜单时，作为侧边栏的兜底菜单
     * @param int $position 显示位置：self::POSITION_LEFT(2) 或 self::POSITION_RIGHT(3)
     * @return array 顶级栏目树，子栏目同样按位置过滤
     */
    public function getPositionMenu(int $position = self::POSITION_LEFT): array
    {
        $menu = [];
        foreach ($this->getTreeArray() as $tree){
            if(!$this->isPositionEnabled($tree, $position)){
                continue;
            }
            if(!empty($tree['children'])){
                $tree['children'] = array_values(array_filter($tree['children'], function ($child) use ($position){
                    return $this->isPositionEnabled($child, $position);
                }));
            }
            $menu[] = $tree;
        }
        return $menu;
    }

    /**
     * 判断栏目 show_position 是否包含指定显示位置
     */
    private function isPositionEnabled(array $catalog, int $position): bool
    {
        $positions = array_map('intval', array_filter(explode(',', $catalog['show_position'] ?? '')));
        return in_array($position, $positions, true);
    }

}
