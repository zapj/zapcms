<?php

namespace zap;

use zap\db\Model;

class NodeMeta extends Model {

    public static function tableName(): string
    {
        return 'node_meta';
    }

    public static function primaryKey()
    {
        return 'meta_id';
    }

}