<?php

namespace zap;

use zap\db\AbstractModel;

class Node extends AbstractModel
{
    protected $table = 'node';

    protected $primaryKey = 'id';


    const PUBLISHED = 1; //已发布
    const DRAFT = 2; //草稿
    const SOFT_DELETE = 3; //软删除

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

    public function getStatusTitle($status): string
    {
        switch ($status){
            case self::PUBLISHED:
                return '已发布';
            case self::DRAFT:
                return '草稿';
            case self::SOFT_DELETE:
                return '软删除';
            default:
                return '无';
        }
    }

    public function getStatus(){
        return [
            self::PUBLISHED => '已发布',
            self::DRAFT => '草稿',
            self::SOFT_DELETE => '已删除'
        ];
    }



}