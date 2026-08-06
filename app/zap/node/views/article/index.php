<?php
use zap\facades\Url;

/**
 * @var array $data
 * @var \zap\helpers\Pagination $page
 * @var int $catalogId
 * @var array $catalogPaths
 * @var array $breadcrumbs
 * @var string $page_title
 * @var string $_controller
 * @var string $_action
 */

$this->layout('layouts/common');

if (!empty($catalogPaths)) {
    $last = array_pop($catalogPaths);
    $page_title = $last['title'] . ' - ' . $page_title;
    $catalogPaths[] = $last;
}
?>

<div class="container-fluid p-0">
    <div class="row g-3">
        <!-- 左侧栏目导航 -->
        <div class="col-lg-3">
            <div class="card card-outline card-info">
                <div class="card-header p-2">
                    <h6 class="card-title mb-0">
                        <i class="fa fa-sitemap me-1 text-info"></i>栏目导航
                    </h6>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height:calc(100vh - 260px);overflow-y:auto;">
                    <?php include __DIR__ . '/../default/sidebar.php'; ?>
                </div>
            </div>
        </div>
        <!-- /左侧栏目导航 -->

        <!-- 右侧内容区 -->
        <div class="col-lg-9">
            <div class="card card-outline card-info">
                <!-- 工具栏 -->
                <div class="card-header p-2">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <!-- 面包屑路径 -->
                        <div class="d-flex align-items-center me-auto">
                            <i class="fa fa-map-marker-alt text-muted me-1"></i>
                            <?php if (!empty($catalogPaths)): ?>
                                <?php
                                $breadcrumbParts = [];
                                foreach ($catalogPaths as $cat):
                                    $breadcrumbParts[] = '<a href="' . Url::action("Node@{$_controller}", ['cid' => $cat['id']]) . '" class="text-muted text-decoration-none">' . htmlspecialchars($cat['title']) . '</a>';
                                endforeach;
                                echo implode(' <span class="text-muted mx-1">/</span> ', $breadcrumbParts);
                                ?>
                            <?php else: ?>
                                <span class="text-muted">全部文章</span>
                            <?php endif; ?>
                        </div>

                        <!-- 搜索框 -->
                        <form method="get" class="d-flex me-1" style="max-width:220px">
                            <?php if ($catalogId): ?>
                            <input type="hidden" name="cid" value="<?php echo $catalogId; ?>">
                            <?php endif; ?>
                            <div class="input-group input-group-sm">
                                <input type="search" name="s" class="form-control" placeholder="搜索标题..."
                                       value="<?php echo htmlspecialchars($_GET['s'] ?? ''); ?>">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </form>

                        <!-- 添加按钮 -->
                        <a href="<?php echo Url::action("Node@{$_controller}/add", ['cid' => $catalogId]); ?>"
                           class="btn btn-info btn-sm">
                            <i class="fa fa-plus me-1"></i>添加文章
                        </a>
                    </div>
                </div>
                <!-- /工具栏 -->

                <!-- 内容表格 -->
                <div class="card-body p-0">
                    <?php if (!empty($data)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:60px">ID</th>
                                    <th>标题</th>
                                    <th style="width:120px">所属栏目</th>
                                    <th style="width:100px">状态</th>
                                    <th style="width:140px">发布时间</th>
                                    <th style="width:130px">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo $row['id']; ?></td>
                                    <td>
                                        <a href="<?php echo Url::action("Node@{$_controller}/edit/{$row['id']}", ['cid' => $catalogId]); ?>"
                                           class="text-decoration-none fw-semibold">
                                            <?php echo htmlspecialchars($row['title']); ?>
                                        </a>
                                        <?php if (!empty($row['slug'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($row['slug']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['catalog_name'])): ?>
                                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['catalog_name']); ?></span>
                                        <?php elseif (!empty($row['catalog_id'])): ?>
                                            <?php $cat = $menu->get($row['catalog_id']); ?>
                                            <?php echo $cat ? '<span class="badge bg-info text-dark">' . htmlspecialchars($cat['title']) . '</span>' : '<span class="text-muted">-</span>'; ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (($row['status'] ?? 1) == 1): ?>
                                        <span class="badge bg-success">已发布</span>
                                        <?php else: ?>
                                        <span class="badge bg-secondary">草稿</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?php echo date('Y-m-d H:i', $row['pub_time'] ?? $row['add_time'] ?? 0); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo Url::action("Node@{$_controller}/edit/{$row['id']}", ['cid' => $catalogId]); ?>"
                                               class="btn btn-outline-info" title="编辑">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger"
                                                    onclick="deleteNode(<?php echo $row['id']; ?>,'<?php echo htmlspecialchars(addslashes($row['title'])); ?>')"
                                                    title="删除">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <!-- 空状态 -->
                    <div class="text-center py-5">
                        <i class="fa fa-newspaper fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-3">暂无文章数据</p>
                        <a href="<?php echo Url::action("Node@{$_controller}/add", ['cid' => $catalogId]); ?>"
                           class="btn btn-info btn-sm">
                            <i class="fa fa-plus me-1"></i>添加第一篇文章
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <!-- /内容表格 -->

                <?php if (!empty($data)): ?>
                <!-- 底部分页信息 -->
                <div class="card-footer p-2 d-flex flex-wrap align-items-center justify-content-between">
                    <small class="text-muted">
                        显示第 <?php echo $page->getOffset() + 1; ?>-<?php echo min($page->getOffset() + $page->getLimit(), $page->total()); ?> 条，共 <?php echo $page->total(); ?> 条
                    </small>
                    <div>
                        <?php echo $page->render(); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- /右侧内容区 -->
    </div>
</div>

<script>
function deleteNode(id, title) {
    if (confirm('确定要删除 "' + title + '" 吗？此操作不可恢复！')) {
        var formData = new FormData();
        formData.append('id', id);
        fetch('<?php echo Url::action("Node@{$_controller}/remove"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.code === 0) {
                location.reload();
            } else {
                alert(res.msg || '删除失败');
            }
        });
    }
}
</script>
