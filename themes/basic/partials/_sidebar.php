<?php defined('IN_ZAPCMS') or die('No permission to access');

// 优先使用传入参数，否则从 PageState 获取
$state = pageState();
$title = htmlspecialchars(
    ($node['title'] ?? '') ?: ($state->node['title'] ?? '')
);
$catalogs = !empty($subCatalogList) ? $subCatalogList : ($state->subCatalogList ?? []);
$currentId = $nodeId ?? $state->nodeId ?? 0;
// 模板页面没有相关联的栏目菜单时，读取左侧导航栏目菜单作为兜底（侧边栏位于页面左侧）
if (empty($catalogs)) {
    $catalogs = \zapcms\services\Catalog::instance()->getPositionMenu(\zapcms\services\Catalog::POSITION_LEFT);
}
if (empty($catalogs)) return;
// 获取 catlogPaths 第一个的title（搜索页等未设置栏目路径时安全降级）
$catalogPaths = $state->catalogPaths ?? [];
$firstCatalog = $catalogPaths[0] ?? null;
$firstCatalogTitle = $firstCatalog['title'] ?? '';

$lastCatalog = !empty($catalogPaths) ? end($catalogPaths) : null;
$lastCatalogId = $lastCatalog['id'] ?? 0;

?>
<aside class="sidebar col-sm-3">
    <div class="widget">
        <h4><?php echo e($firstCatalogTitle ?? $title); ?></h4>
        <ul>
            <?php foreach ($catalogs as $catalog): ?>
                <li<?php if ($lastCatalogId == $catalog['id']) echo ' class="current"'; ?>>
                    <a href="<?php echo site_url('/' . $catalog['slug']); ?>"
                       title="<?php echo htmlspecialchars($catalog['title']); ?>">
                        <?php echo htmlspecialchars($catalog['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>
