<?php

namespace zap;

use zap\db\Model;

class Node extends Model
{
    protected $table = 'node';

    protected $primaryKey = 'id';

    const STATUS_PUBLISH = 'publish'; //已发布
    const STATUS_DRAFT = 'draft'; //草稿
    const STATUS_SOFT_DELETE = 'soft_delete'; //软删除
    const STATUS_TRASH = 'trash'; //软删除

    public static function tableName(): string
    {
        return 'node';
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
        return static::createQuery()
            ->select('node_type,count(id)')
            ->groupBy('node_type')
            ->get(FETCH_KEY_PAIR);
    }






}