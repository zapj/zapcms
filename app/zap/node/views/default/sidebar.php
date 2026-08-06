<?php
use zap\facades\Url;
use zap\cms\Catalog;

/**
 * @var \zap\cms\Catalog $menu
 * @var int $catalogId
 */
$menu = $menu ?? Catalog::instance();
$catalogId = $catalogId ?? 0;

$tree = $menu->getTreeArray();

/**
 * 递归收集选中栏目的所有祖先 id，用于展开路径
 */
function collectAncestorIds($tree, $activeId) {
    $path = [];
    foreach ($tree as $item) {
        if (isset($item['id']) && $item['id'] == $activeId) {
            return [$item['id']];
        }
        if (!empty($item['children'])) {
            $childPath = collectAncestorIds($item['children'], $activeId);
            if (!empty($childPath)) {
                $path[] = $item['id'];
                $path = array_merge($path, $childPath);
                return $path;
            }
        }
    }
    return $path;
}

// 获取要展开的节点 id 集合
$expandIds = [];
if ($catalogId > 0) {
    $expandIds = collectAncestorIds($tree, $catalogId);
}
$expandIdsMap = array_flip($expandIds);

/**
 * 递归渲染树节点
 */
function renderTree($items, $catalogId, $expandIdsMap, $level = 0) {
    // 每层级缩进：base + level * step（用 inline style 更精确）
    $indentMap = [0 => 0.5, 1 => 2.5, 2 => 4.5, 3 => 6.5];
    foreach ($items as $item):
        $hasChildren = !empty($item['children']);
        $isActive = $catalogId == $item['id'];
        $isExpanded = $isActive || isset($expandIdsMap[$item['id']]);
        $paddingLeft = $indentMap[$level] ?? (2.5 + ($level - 2) * 2);
        // catalog 表中 node_type 存的是真实内容类型（page/article/product 等）
        $nodeType = $item['node_type'] ?? 'default';
        $isPage = $nodeType === 'page';
?>
    <div class="tree-node">
        <a href="<?php echo Url::action("Node@{$nodeType}", ['cid' => $item['id']]); ?>"
           class="list-group-item list-group-item-action border-0 d-flex align-items-center <?php echo $isActive ? 'active' : ''; ?>"
           data-catalog-id="<?php echo $item['id']; ?>"
           style="padding-left: <?php echo $paddingLeft; ?>rem;">
            <?php if ($hasChildren): ?>
            <span class="tree-toggle me-1" onclick="toggleTreeNode(event, this)" style="cursor:pointer;width:16px;text-align:center;flex-shrink:0;">
                <i class="fa fa-caret-<?php echo $isExpanded ? 'down' : 'right'; ?>"></i>
            </span>
            <?php else: ?>
            <span style="width:16px" class="me-1 flex-shrink-0"></span>
            <?php endif; ?>
            <?php if ($isPage): ?>
            <i class="far fa-file me-1 text-info" style="width:14px;flex-shrink-0;"></i>
            <?php else: ?>
            <i class="far fa-folder me-1 text-warning" style="width:14px;flex-shrink-0;"></i>
            <?php endif; ?>
            <span class="flex-grow-1 text-truncate"><?php echo htmlspecialchars($item['title']); ?></span>
        </a>
        <?php if ($hasChildren): ?>
        <div class="tree-children<?php echo $isExpanded ? '' : ' d-none'; ?>">
            <?php renderTree($item['children'], $catalogId, $expandIdsMap, $level + 1); ?>
        </div>
        <?php endif; ?>
    </div>
<?php
    endforeach;
}
?>

<div class="p-2">
    <a href="<?php echo Url::action("Node@{$_controller}", ['cid' => 0]); ?>"
       class="btn btn-outline-secondary btn-sm w-100 <?php echo !$catalogId ? 'active' : ''; ?>">
        <i class="fa fa-home me-1"></i>全部内容
    </a>
</div>

<div class="list-group list-group-flush">
    <?php if (!empty($tree)): ?>
        <?php renderTree($tree, $catalogId, $expandIdsMap); ?>
    <?php else: ?>
    <div class="text-center text-muted py-4">
        <i class="fa fa-inbox fa-2x d-block mb-2"></i>
        <small>暂无栏目</small>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleTreeNode(e, el) {
    e.preventDefault();
    e.stopPropagation();
    var children = el.closest('.tree-node').querySelector('.tree-children');
    var icon = el.querySelector('i');
    if (children) {
        children.classList.toggle('d-none');
        if (icon) {
            if (children.classList.contains('d-none')) {
                icon.classList.remove('fa-caret-down');
                icon.classList.add('fa-caret-right');
            } else {
                icon.classList.remove('fa-caret-right');
                icon.classList.add('fa-caret-down');
            }
        }
    }
}
</script>
