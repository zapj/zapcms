<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> — 商城</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">首页</a></li>
            <li class="breadcrumb-item"><a href="/mod/shop/product/lists">产品列表</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($product['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <?php if (!empty($product['image'])): ?>
        <div class="col-md-5">
            <img src="<?= htmlspecialchars($product['image']) ?>"
                 class="img-fluid rounded" alt="<?= htmlspecialchars($product['title']) ?>">
        </div>
        <?php endif; ?>

        <div class="col-md-7">
            <h3><?= htmlspecialchars($product['title']) ?></h3>

            <div class="my-3">
                <?php if ($product['origin_price'] > $product['price']): ?>
                <span class="text-decoration-line-through text-muted me-2 fs-5">
                    ¥<?= number_format($product['origin_price'], 2) ?>
                </span>
                <?php endif; ?>
                <span class="text-danger fw-bold fs-3">¥<?= number_format($product['price'], 2) ?></span>
                <span class="text-muted ms-2">/ <?= htmlspecialchars($product['unit'] ?? '件') ?></span>
            </div>

            <div class="mb-3">
                <span class="text-muted">库存：<?= (int) $product['stock'] ?> <?= htmlspecialchars($product['unit'] ?? '件') ?></span>
            </div>

            <form action="/mod/shop/cart/add" method="post" class="d-flex align-items-center gap-2 mb-4">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="input-group" style="width:130px">
                    <button type="button" class="btn btn-outline-secondary" onclick="this.nextElementSibling.stepDown()">−</button>
                    <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>"
                           class="form-control text-center">
                    <button type="button" class="btn btn-outline-secondary" onclick="this.previousElementSibling.stepUp()">+</button>
                </div>
                <button type="submit" class="btn btn-danger btn-lg">
                    <i class="bi bi-cart"></i> 加入购物车
                </button>
            </form>

            <?php if (!empty($product['summary'])): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">产品简介</h6>
                    <p class="card-text text-muted"><?= htmlspecialchars($product['summary']) ?></p>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($product['content'])): ?>
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">产品详情</h6>
                    <div><?= ($product['content']) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
