<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:26
 * @lastModified 2023/12/20 下午6:34
 *
 */

namespace zap\cms\models;

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