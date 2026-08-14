<?php
use zapcms\helpers\ThumbHelper;

// 主图：原图存在则生成对应尺寸缩略图（原名+尺寸），不存在则显示占位图原名，不产生重复缩略图
// 尺寸从内容模型显示配置读取（默认 750x500）
$detailW = (int)\zapcms\services\NodeType::getConfig($product['node_type'] ?? 'product', 'detail_image_width', 750);
$detailH = (int)\zapcms\services\NodeType::getConfig($product['node_type'] ?? 'product', 'detail_image_height', 500);
$imageUrl = ThumbHelper::thumb($product['image'] ?? '', $detailW, $detailH);
$stock    = (int) ($product['stock'] ?? 0);
$unit     = htmlspecialchars($product['unit'] ?? '件');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> — 商城</title>
    <link href="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-main-img {
            width: 100%;
            height: 360px;
            object-fit: cover;
            border-radius: .5rem;
            background: #f5f6fa;
        }
        @media (max-width: 767.98px) {
            .product-main-img { height: 260px; }
        }
    </style>
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
        <div class="col-md-5">
            <img src="<?= htmlspecialchars($imageUrl) ?>" class="product-main-img" alt="<?= htmlspecialchars($product['title']) ?>">
        </div>

        <div class="col-md-7">
            <h3><?= htmlspecialchars($product['title']) ?></h3>

            <div class="my-3">
                <?php if (!empty($product['origin_price']) && floatval($product['origin_price']) > floatval($product['price'])): ?>
                <span class="text-decoration-line-through text-muted me-2 fs-5">
                    ¥<?= number_format(floatval($product['origin_price']), 2) ?>
                </span>
                <?php endif; ?>
                <span class="text-danger fw-bold fs-3">¥<?= number_format(floatval($product['price']), 2) ?></span>
                <span class="text-muted ms-2">/ <?= $unit ?></span>
            </div>

            <div class="mb-3">
                <?php if ($stock > 0): ?>
                <span class="text-success"><i class="bi bi-check-circle"></i> 有货（<?= $stock ?> <?= $unit ?>）</span>
                <?php else: ?>
                <span class="text-danger"><i class="bi bi-x-circle"></i> 暂时缺货</span>
                <?php endif; ?>
            </div>

            <form action="/mod/shop/cart/add" method="post" class="d-flex align-items-center gap-2 mb-4">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="input-group" style="width:130px">
                    <button type="button" class="btn btn-outline-secondary" onclick="this.nextElementSibling.stepDown()">−</button>
                    <input type="number" name="quantity" value="1" min="1" max="<?= $stock ?: 1 ?>"
                           class="form-control text-center" <?= $stock <= 0 ? 'disabled' : '' ?>>
                    <button type="button" class="btn btn-outline-secondary" onclick="this.previousElementSibling.stepUp()">+</button>
                </div>
                <button type="submit" class="btn btn-danger btn-lg" <?= $stock <= 0 ? 'disabled' : '' ?>>
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
