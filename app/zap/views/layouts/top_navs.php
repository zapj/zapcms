<aside class="app-sidebar bg-dark shadow" data-bs-theme="dark">
    <!-- 侧边栏品牌区域 -->
    <div class="sidebar-brand">
        <a href="<?php echo \zap\facades\Url::action('Index'); ?>" class="brand-link text-decoration-none">
            <img src="<?php echo base_url();?>/assets/admin/img/zap_logo_white.svg" alt="ZAP" class="brand-image opacity-75 shadow" width="26" height="26" onerror="this.style.display='none'">
            <span class="brand-text fw-light ms-2">CMS</span>
        </a>
    </div>

    <!-- 侧边栏菜单滚动区 -->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="<?php echo \zap\facades\Url::action('Index'); ?>" class="nav-link">
                            <i class="nav-icon fa fa-dashboard"></i>
                            <p>控制面板</p>
                        </a>
                </li>
                <?php
                use zap\facades\Url;
                use zap\cms\AdminMenu;

                $menuTree = AdminMenu::instance()->getTreeArray();
                

                foreach ($menuTree as $group):
                    $groupTitle = $group['title'] ?? $group['name'] ?? '';
                    if (empty($groupTitle)) continue;

                    $children = $group['children'] ?? [];
                    $hasChildren = !empty($children);
                    $groupAction = Url::action($group['link_to'] ?? '');

                    // 判断当前 group 自身或任意子节点是否激活
                    // active_rule 优先 → 正则匹配 controller/method；否则用 URL 匹配
                    $groupActive = false;
                    if (!$hasChildren) {
                        if ($group['active_rule'] ?? '') {
                            $groupActive = Url::active($group['active_rule']);
                        } elseif ($groupAction && $groupAction !== '#') {
                            $groupActive = Url::active($groupAction);
                        }
                    }
                    echo "\n<!-- Menu Group: " . htmlspecialchars($groupTitle) . " -->\n";
                    echo "<!-- Group Active: " . ($groupActive ? 'true' : 'false') . " -->\n";

                    // 检查子节点激活状态，并决定父级是否展开
                    $childActiveAny = false;
                    if ($hasChildren):
                        foreach ($children as $child):
                            $childActive = false;
                            if ($child['active_rule'] ?? '') {
                                $childActive = Url::active($child['active_rule']);
                            } else {
                                $childAction = Url::action($child['link_to'] ?? '');
                                $childActive = ($childAction && $childAction !== '#') ? Url::active($childAction) : false;
                            }
                            if ($childActive) {
                                $childActiveAny = true;
                                $groupActive = true; // 父级也标记为激活
                                break;
                            }
                        endforeach;
                    endif;

                    // 无 action 的菜单项用 # 占位，确保可点击
                    if (empty($groupAction)) {
                        $groupAction = '#';
                    }

                    // 父级 li 的 class
                    $liClass = 'nav-item';
                    if ($hasChildren) {
                        $liClass .= $childActiveAny ? ' menu-open' : '';
                    }

                    // 父级链接 class
                    $linkClass = 'nav-link';
                    if (!$hasChildren && $groupActive) {
                        $linkClass .= ' active';
                    } elseif ($hasChildren && $groupActive) {
                        $linkClass .= ' active';
                    }

                    // 图标
                    $icon = $group['icon'] ?? 'fas fa-circle';
                    if (strpos($icon, 'fa ') !== 0 && !strpos($icon, 'fas ') && !strpos($icon, 'far ') && !strpos($icon, 'fab ')) {
                        $icon = 'fa ' . $icon;
                    }
                ?>
                    <li class="<?php echo $liClass; ?>">
                        <?php if ($hasChildren): ?>
                        <a href="#" class="<?php echo $linkClass; ?>" role="button">
                            <i class="nav-icon <?php echo htmlspecialchars($icon); ?>"></i>
                            <p>
                                <?php echo htmlspecialchars($groupTitle); ?>
                                <i class="nav-arrow fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php foreach ($children as $child):
                                $childTitle = $child['title'] ?? $child['name'] ?? '';
                                $childAction = Url::action($child['link_to'] ?? '#');
                                $childActive = Url::active($child['active_rule'] ?: $childAction) ? ' active' : '';
                                $childIcon = $child['icon'] ?? 'far fa-circle';
                            ?>
                            <li class="nav-item">
                                <a href="<?php echo $childAction === '#' ? '#' : $childAction; ?>" class="nav-link<?php echo $childActive; ?>">
                                    <i class="nav-icon <?php echo htmlspecialchars($childIcon); ?>"></i>
                                    <p><?php echo htmlspecialchars($childTitle); ?></p>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <a href="<?php echo $groupAction === '#' ? '#' : $groupAction; ?>" class="<?php echo $linkClass; ?>">
                            <i class="nav-icon <?php echo htmlspecialchars($icon); ?>"></i>
                            <p><?php echo htmlspecialchars($groupTitle); ?></p>
                        </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</aside>
