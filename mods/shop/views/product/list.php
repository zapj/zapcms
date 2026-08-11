<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>产品列表 — 商城</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">
    <h2 class="mb-4">产品列表</h2>

    <?php if (empty($list)): ?>
        <div class="alert alert-info">暂无产品</div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($list as $item): ?>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($item['image'])): ?>
                <a href="/mod/shop/product/view/<?= htmlspecialchars($item['slug']) ?>">
                    <img src="<?= htmlspecialchars($item['image']) ?>" class="card-img-top"
                         style="height:200px;object-fit:cover" alt="<?= htmlspecialchars($item['title']) ?>">
                </a>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">
                        <a href="/mod/shop/product/view/<?= htmlspecialchars($item['slug']) ?>"
                           class="text-decoration-none text-dark">
                            <?= htmlspecialchars($item['title']) ?>
                        </a>
                    </h6>
                    <p class="card-text small text-muted flex-grow-1">
                        <?= mb_substr(strip_tags($item['summary'] ?? ''), 0, 50) ?>
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="text-danger fw-bold fs-5">¥<?= number_format($item['price'], 2) ?></span>
                        <form action="/mod/shop/cart/add" method="post" style="display:inline">
                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="btn btn-sm btn-primary">加入购物车</button>
                        </form>
                    </div>
                    <?php if ($item['origin_price'] > $item['price']): ?>
                    <small class="text-decoration-line-through text-muted">
                        原价 ¥<?= number_format($item['origin_price'], 2) ?>
                    </small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
