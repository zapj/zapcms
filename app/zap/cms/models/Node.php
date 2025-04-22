<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:26
 * @lastModified 2023/12/27 上午11:25
 *
 */

namespace zap\cms\models;

use zap\DB;
use zap\db\Model;
use zap\db\Query;

class Node extends Model
{

    const STATUS_PUBLISH = 'publish'; //已发布
    const STATUS_DRAFT = 'draft'; //草稿
    const STATUS_SOFT_DELETE = 'soft_delete'; //软删除
    const STATUS_TRASH = 'trash'; //软删除

    public ?int $id = null;

    public static function tableName(): string
    {
        return 'node';
    }

    public static function primaryKey()
    {
        return 'id';
    }

    public function getPubTimeToDate(){
        if($this->hasAttribute('pub_time')){
            return date(Z_DATE_TIME,$this->getAttribute('pub_time'));
        }
        return date(Z_DATE_TIME);
    }

    public static function getStatusTitle($status): string
    {
        switch ($status){
            case self::STATUS_PUBLISH:
                return '已发布';
            case self::STATUS_DRAFT:
                return '草稿';
            case self::STATUS_SOFT_DELETE:
                return '软删除';
            default:
                return '无';
        }
    }

    public function getStatus(): array
    {
        return [
            self::STATUS_PUBLISH => '已发布',
            self::STATUS_DRAFT => '草稿',
            self::STATUS_SOFT_DELETE => '已删除',
            self::STATUS_TRASH => '回收站'
        ];
    }


    public function getAllTypesCount(){
        $resultNodeTypes = static::createQuery()
            ->select('node_type,count(id) as total')
            ->whereNotIn('node_type',["'catalog'"])
            ->groupBy('node_type')
            ->get(FETCH_KEY_PAIR);
//        SELECT node_type,count(id) FROM `zap_catalog` GROUP BY node_type
        $catalogResult = DB::table('catalog')->select('node_type,count(id)')->groupBy('node_type')
            ->fetchAll(FETCH_KEY_PAIR);

        $resultNodeTypes['page'] = ($resultNodeTypes['page'] ?? 0) + arr_get($catalogResult,'page',0);
        return $resultNodeTypes;
    }

    public function getPages($columns = '*'){
        $query = Node::where('node_type','page')->select($columns);
        $query->orWhere(function(Query $query){
            $query->where('node_type','catalog')->where('mime_type','page');
        });
        return $query->get(FETCH_ASSOC);
    }






}