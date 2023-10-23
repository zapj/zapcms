<?php

namespace zap\rbac;

use zap\Categories;
use zap\traits\SingletonTrait;

class Permissions extends Categories
{

    use SingletonTrait;

    public function __construct()
    {
        parent::__construct('permissions','taxonomy_path','perm_id');
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

/*

SELECT GROUP_CONCAT(c2.`title` ORDER BY cp.`level` SEPARATOR ' > ') AS `name`,cp.`perm_id`,  c2.`pid`,cp.level FROM zap_permissions_path cp
LEFT JOIN zap_permissions c2 ON (cp.`path_id` = c2.`perm_id`)
WHERE 1
 */