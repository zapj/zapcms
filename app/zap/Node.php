<?php

namespace zap;

use zap\db\AbstractModel;

class Node extends AbstractModel
{
    protected $table = 'node';

    protected $primaryKey = 'id';

    public static function tableName()
    {
        return 'node';
    }

    public function getPubTimeToDate(){
        if($this->hasAttribute('pub_time')){
            return date(Z_DATE_TIME,$this->getAttribute('pub_time'));
        }
        return date(Z_DATE_TIME);
    }


}