<?php defined('IN_ZAPCMS') or die('No permission to access');

/**
 * 导航菜单 partial
 * 使用时需在外层传入 $catalogMenu 变量
 *
 * @var array $catalogMenu 栏目菜单数据
 */
$state = pageState();
$childLastId = [];
?>
<nav class="navbar navbar-default">
    <div class="container">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse">
            <span class="sr-only">Toggle Navigation</span>
            <i class="fa fa-bars"></i>
        </button>
        <a href="<?php echo home_url(); ?>" class="navbar-brand">
            <img src="<?php echo theme_url(); ?>/img/zap_logo_green.svg" width="120" alt="ZAP CMS Logo">
        </a>
        <div class="collapse navbar-collapse" id="bs-navbar-collapse">
            <ul class="nav navbar-nav main-navbar-nav">
                <li<?php if ($state->isHome) echo ' class="active"'; ?>>
                    <a href="<?php echo base_url('/'); ?>">首页</a>
                </li>
                <?php while ($menu = array_shift($catalogMenu)): ?>
                <li class="nav-item<?php
                    echo !empty($menu['children']) ? ' dropdown' : '';
                    echo ($menu['id'] === $state->nodeId || $state->nodeId === $menu['link_object']) ? ' active' : '';
                ?>">
                    <a data-id="<?php echo $menu['id']; ?>"
                       href="<?php echo smart_node_url($menu); ?>"
                       title="<?php echo htmlspecialchars($menu['title']); ?>"
                       <?php if (!empty($menu['children'])): ?>
                       class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false"
                       <?php elseif (count($childLastId)): ?>
                       class="dropdown-item"
                       <?php else: ?>
                       class="nav-link"
                       <?php endif; ?>>
                        <?php echo htmlspecialchars($menu['title']); ?>
                        <?php if (!empty($menu['children'])): ?>
                            <span class="navbar-right-btn"><span class="caret"></span></span>
                        <?php endif; ?>
                    </a>
                    <?php if (!empty($menu['children'])): ?>
                        <ul class="dropdown-menu">
                        <?php
                        $childLastId[] = end($menu['children'])['id'];
                        while ($children = array_pop($menu['children'])) {
                            array_unshift($catalogMenu, $children);
                        }
                        ?>
                    <?php endif; ?>

                    <?php if ($menu['id'] == end($childLastId)): ?>
                        </ul>
                        <?php array_pop($childLastId); ?>
                    <?php endif; ?>
                </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
</nav>
