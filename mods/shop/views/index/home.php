<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商城首页</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">
    <div class="text-center py-5 bg-light rounded mb-4">
        <h1 class="display-4">欢迎来到商城</h1>
        <p class="lead text-muted">ZAP CMS 模块化商城演示</p>
        <div class="mt-3">
            <a href="/mod/shop/product/lists" class="btn btn-primary btn-lg me-2">查看产品</a>
            <a href="/mod/shop/cart" class="btn btn-outline-success btn-lg">
                🛒 购物车 <span class="badge bg-danger">3</span>
            </a>
        </div>
    </div>

    <?php if (!empty($list)): ?>
    <h3 class="mb-3">推荐产品</h3>
    <div class="row g-4">
        <?php foreach ($list as $item): ?>
        <div class="col-md-3 col-sm-6">
            <div class="card h-100 shadow-sm">
                <a href="/mod/shop/product/view/<?= htmlspecialchars($item['slug']) ?>">
                    <?php if (!empty($item['image'])): ?>
                    <img src="<?= htmlspecialchars($item['image']) ?>" class="card-img-top"
                         style="height:180px;object-fit:cover">
                    <?php else: ?>
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:180px">
                        <span class="text-muted">暂无图片</span>
                    </div>
                    <?php endif; ?>
                </a>
                <div class="card-body">
                    <h6><?= htmlspecialchars($item['title']) ?></h6>
                    <span class="text-danger fw-bold">¥<?= number_format($item['price'], 2) ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

</body>
</html>
