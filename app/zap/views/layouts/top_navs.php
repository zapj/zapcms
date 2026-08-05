<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
    <!-- 侧边栏品牌区域 -->
    <div class="sidebar-brand">
        <a href="<?php echo \zap\facades\Url::action('System@index'); ?>" class="brand-link text-decoration-none">
            <img src="<?php echo base_url();?>/assets/admin/img/zap_logo_white.svg" alt="ZAP" class="brand-image opacity-75 shadow" width="26" height="26" onerror="this.style.display='none'">
            <span class="brand-text fw-light ms-2">ZAP CMS</span>
        </a>
    </div>

    <!-- 侧边栏菜单滚动区 -->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <?php
                use zap\facades\Url;

                $menuTree = \zap\cms\AdminMenu::instance()->getTreeArray();
                $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
                
                foreach ($menuTree as $group):
                    // 分组标题
                    if (!empty($group['title']) && !empty($group['link_to']) && $group['link_to'] !== '#'):
                        $groupActive = Url::active('', '', $group['active_rule'] ?? '', 'active');
                        $groupUrl = $group['link_to'];
                ?>
                    <li class="nav-item<?php echo $groupActive ? ' menu-open' : ''; ?>">
                        <a href="<?php echo $groupUrl; ?>" class="nav-link<?php echo $groupActive && empty($group['children']) ? ' active' : ''; ?>">
                            <?php if (!empty($group['icon'])): ?>
                            <i class="nav-icon fa <?php echo htmlspecialchars($group['icon']); ?>"></i>
                            <?php else: ?>
                            <i class="nav-icon fa fa-circle"></i>
                            <?php endif; ?>
                            <p>
                                <?php echo htmlspecialchars($group['title']); ?>
                                <?php if (!empty($group['children'])): ?>
                                <i class="nav-arrow fa fa-angle-left"></i>
                                <?php endif; ?>
                            </p>
                        </a>
                        <?php if (!empty($group['children'])): ?>
                        <ul class="nav nav-treeview">
                            <?php foreach ($group['children'] as $child): 
                                $childActive = Url::active('', '', $child['active_rule'] ?? '', 'active');
                                $childUrl = $child['link_to'] ?? '#';
                            ?>
                            <li class="nav-item">
                                <a href="<?php echo $childUrl; ?>" class="nav-link<?php echo $childActive; ?>">
                                    <i class="nav-icon fa fa-circle-notch"></i>
                                    <p><?php echo htmlspecialchars($child['title']); ?></p>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </li>
                <?php elseif (!empty($group['title'])): ?>
                    <!-- 纯文本分组标题 -->
                    <li class="nav-header text-uppercase"><?php echo htmlspecialchars($group['title']); ?></li>
                <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
