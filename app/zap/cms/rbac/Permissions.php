<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author        Allen
 * @email        zapcms@zap.cn
 * @date        2023/12/27 上午11:01
 * @lastModified        2023/10/25 上午11:20
 *
 */

namespace zap\cms\rbac;

use zap\cms\Categories;
use zap\traits\SingletonTrait;

class Permissions extends Categories
{

    use SingletonTrait;

    public function __construct()
    {
        parent::__construct('permissions','permissions_path','perm_id');
    }

    public function add($data): int
    {
        $data['updated_at'] = time();
        $data['created_at'] = time();
        return parent::add($data);
    }

    public function update($data, $id)
    {
        $data['updated_at'] = time();
        return parent::update($data, $id);
    }
}
