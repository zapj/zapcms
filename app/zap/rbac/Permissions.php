<?php

namespace zap\rbac;

use zap\Categories;
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
