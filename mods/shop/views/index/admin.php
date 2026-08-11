<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商城仪表盘</title>
</head>
<body>
<div class="container-fluid p-3">
    <h4 class="mb-4">商城仪表盘</h4>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card border-start border-primary border-3 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">产品总数</div>
                    <div class="fs-3 fw-bold text-primary"><?= $productCount ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-3 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold">上架中</div>
                    <div class="fs-3 fw-bold text-success"><?= $onlineCount ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="/z-admin/mod/shop/product" class="btn btn-primary">
            <i class="fa fa-cubes"></i> 管理产品
        </a>
    </div>
</div>
</body>
</html>
