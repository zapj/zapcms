<?php defined('IN_ZAP_CMS') or die('No permission to access');

// 优先使用传入参数，否则从 PageState 获取
$state = pageState();
$title = htmlspecialchars(
    ($node['title'] ?? '') ?: ($state->node['title'] ?? '')
);
$catalogs = !empty($subCatalogList) ? $subCatalogList : ($state->subCatalogList ?? []);
$currentId = $nodeId ?? $state->nodeId ?? 0;

if (empty($catalogs)) return;
?>
<aside class="sidebar col-sm-3">
    <div class="widget">
        <h4><?php echo $title; ?></h4>
        <ul>
            <?php foreach ($catalogs as $catalog): ?>
                <li<?php if ($currentId == $catalog['id']) echo ' class="current"'; ?>>
                    <a href="<?php echo site_url('/' . $catalog['slug']); ?>"
                       title="<?php echo htmlspecialchars($catalog['title']); ?>">
                        <?php echo htmlspecialchars($catalog['title']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>
