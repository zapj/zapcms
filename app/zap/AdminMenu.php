<?php

namespace app\zap;

use zap\Category;

class AdminMenu extends Category
{
    public function __construct()
    {
        parent::__construct('admin_menu');
    }

}