<?php

namespace zap\rbac;

use zap\Category;
use zap\traits\SingletonTrait;

class Permissions extends Category
{

    use SingletonTrait;

    public function __construct()
    {
        parent::__construct('permissions','perm_id');
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