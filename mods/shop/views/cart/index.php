<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>购物车 — 商城</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">
    <h2 class="mb-4">购物车 <small class="text-muted fs-6">(<?= $count ?> 件)</small></h2>

    <?php if (empty($items)): ?>
        <div class="text-center py-5">
            <p class="text-muted fs-5">购物车是空的</p>
            <a href="/mod/shop/product/lists" class="btn btn-primary">去逛逛</a>
        </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="table-light">
                <tr>
                    <th>商品</th>
                    <th style="width:120px">单价</th>
                    <th style="width:150px">数量</th>
                    <th style="width:120px">小计</th>
                    <th style="width:80px">操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <?php if (!empty($item['image'])): ?>
                            <img src="<?= htmlspecialchars($item['image']) ?>" class="rounded me-3"
                                 style="width:60px;height:60px;object-fit:cover">
                            <?php endif; ?>
                            <span><?= htmlspecialchars($item['title']) ?></span>
                        </div>
                    </td>
                    <td>¥<?= number_format($item['price'], 2) ?></td>
                    <td>
                        <form action="/mod/shop/cart/update" method="post" class="d-flex gap-1">
                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                            <input type="number" name="quantity" value="<?= $item['quantity'] ?>"
                                   min="1" class="form-control form-control-sm text-center" style="width:70px"
                                   onchange="this.form.submit()">
                        </form>
                    </td>
                    <td class="text-danger fw-semibold">¥<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    <td>
                        <a href="/mod/shop/cart/remove/<?= $item['id'] ?>"
                           class="btn btn-sm btn-outline-danger">删除</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="3" class="text-end fw-bold">合计：</td>
                    <td class="text-danger fw-bold fs-5">¥<?= number_format($total, 2) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="d-flex justify-content-between mt-3">
        <a href="/mod/shop/product/lists" class="btn btn-outline-secondary">继续购物</a>
        <div>
            <a href="/mod/shop/cart/clear" class="btn btn-outline-warning me-2">清空购物车</a>
            <button type="button" class="btn btn-danger btn-lg" disabled>结算（开发中）</button>
        </div>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
