<?php

namespace zap;

use zap\db\Model;

class NodeRelation extends Model
{

    public static function tableName(): string
    {
        return 'node_relation';
    }

    public static function primaryKey()
    {
        return ['catalog_id','node_id'];
    }
}