<?php

namespace zap;

use zap\db\AbstractModel;

class Content extends AbstractModel
{
    protected $table = 'contents';

    protected $primaryKey = 'id';

    public static function tableName()
    {
        return 'contents';
    }


}