<?php
/**
 * 购物车控制器
 *
 * 前端路由: /mod/shop/cart/... 或 /shop/cart/...
 */

namespace mods\shop\controllers;

use mods\shop\models\Cart;
use zap\view\View;

class CartController
{
    /**
     * 购物车页面
     */
    public function index(): void
    {
        View::render('cart.index', [
            'items' => Cart::all(),
            'total' => Cart::total(),
            'count' => Cart::count(),
        ]);
    }

    /**
     * 添加到购物车（AJAX / POST）
     */
    public function add(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity  = max(1, (int) ($_POST['quantity'] ?? 1));

        Cart::add($productId, $quantity);

        if ($this->isAjax()) {
            echo json_encode(['code' => 0, 'msg' => '已加入购物车', 'count' => Cart::count()]);
            return;
        }

        header('Location: /mod/shop/cart');
    }

    /**
     * 更新数量（AJAX / POST）
     */
    public function update(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $quantity  = (int) ($_POST['quantity'] ?? 1);

        Cart::update($productId, $quantity);

        if ($this->isAjax()) {
            echo json_encode([
                'code'  => 0,
                'total' => number_format(Cart::total(), 2),
                'count' => Cart::count(),
            ]);
            return;
        }

        header('Location: /mod/shop/cart');
    }

    /**
     * 移除（AJAX / GET）
     */
    public function remove(int $productId = 0): void
    {
        Cart::remove($productId);

        if ($this->isAjax()) {
            echo json_encode(['code' => 0, 'msg' => '已移除']);
            return;
        }

        header('Location: /mod/shop/cart');
    }

    /**
     * 是否为 AJAX 请求
     */
    private function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || strtolower($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';
    }
}
