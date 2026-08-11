<?php
/**
 * 商城模块 - 卸载脚本
 */

use zap\db\DB;

DB::schema()->dropIfExists('shop_order_item');
DB::schema()->dropIfExists('shop_order');
DB::schema()->dropIfExists('shop_product');

return true;
