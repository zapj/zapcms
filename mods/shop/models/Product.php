<?php
/**
 * 产品模型
 */

namespace mods\shop\models;

use zap\db\Model;

class Product extends Model
{
    protected $table = 'shop_product';

    protected $primaryKey = 'id';

    /** 状态：上架 */
    const STATUS_ON  = 1;
    /** 状态：下架 */
    const STATUS_OFF = 0;

    /**
     * 分页获取产品列表
     */
    public static function getList(int $page = 1, int $size = 12): array
    {
        $query = static::createQuery()->where('status', self::STATUS_ON)->orderBy('sort DESC, id DESC');

        $total = $query->count();
        $list  = $query->limit($size, ($page - 1) * $size)->get();

        return [
            'list'     => $list,
            'total'    => $total,
            'page'     => $page,
            'pageSize' => $size,
            'pages'    => (int) ceil($total / $size),
        ];
    }

    /**
     * 根据 slug 获取产品
     */
    public static function findBySlug(string $slug): ?array
    {
        return static::createQuery()->where('slug', $slug)->where('status', self::STATUS_ON)->first();
    }

    /**
     * 获取价格格式化
     */
    public static function priceFormat(float $price): string
    {
        return '¥' . number_format($price, 2);
    }
}
