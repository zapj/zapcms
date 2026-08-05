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
                use zap\cms\AdminMenu;

                $menuTree = AdminMenu::instance()->getTreeArray();

                // 修正 link_to：数据库列名是 link_to（非 action 列），去掉多余的 admin/ 前缀
                $fixAction = function($link_to) {
                    if (empty($link_to)) return '';
                    $link_to = ltrim($link_to, '/');
                    if (str_starts_with($link_to, 'admin/')) {
                        $link_to = substr($link_to, 6);
                    }
                    return $link_to;
                };

                // 修正 active_rule：
                //   1. 去掉括号 (node/.*) → node/.*
                //   2. admin/ → z-admin/
                //   3. @ → /
                //   4. 补充 z-admin/ 前缀
                //   5. 正则 .* → 简单 *  (urlMatch 只支持末尾单 * 前缀匹配)
                //   6. 补充前导 /  (current() 返回 /z-admin/xxx)
                $fixRule = function($rule) {
                    if (empty($rule)) return '';
                    $rule = ltrim($rule, '/');
                    $rule = trim($rule, '()');
                    if (str_starts_with($rule, 'admin/')) {
                        $rule = 'z-' . $rule;
                    }
                    $rule = str_replace('@', '/', $rule);
                    if (!str_starts_with($rule, 'z-admin/')) {
                        $rule = 'z-admin/' . $rule;
                    }
                    // urlMatch 只能识别末尾单个 *（非正则 .*）
                    $rule = preg_replace('#/\.\*$#', '*', $rule);
                    if (!str_starts_with($rule, '/')) {
                        $rule = '/' . $rule;
                    }
                    return $rule;
                };

                foreach ($menuTree as $group):
                    $groupTitle = $group['title'] ?? $group['name'] ?? '';
                    if (empty($groupTitle)) continue;

                    $children = $group['children'] ?? [];
                    $hasChildren = !empty($children);
                    $groupAction = $fixAction($group['link_to'] ?? '');
                    $groupRule  = $fixRule($group['active_rule'] ?? $groupAction);

                    // 判断当前 group 自身或任意子节点是否激活
                    $groupActive = false;
                    if (!$hasChildren && $groupRule) {
                        $groupActive = (bool)Url::isActive($groupRule);
                    }

                    // 检查子节点激活状态，并决定父级是否展开
                    $childActiveAny = false;
                    if ($hasChildren):
                        foreach ($children as $child):
                            $childRule = $fixRule($child['active_rule'] ?? $fixAction($child['link_to'] ?? ''));
                            if ($childRule && Url::isActive($childRule)) {
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
                                $childAction = $fixAction($child['link_to'] ?? '#');
                                $childRule = $fixRule($child['active_rule'] ?? $childAction);
                                $childActive = $childRule ? Url::isActive($childRule, ' active') : '';
                                $childIcon = $child['icon'] ?? 'far fa-circle';
                            ?>
                            <li class="nav-item">
                                <a href="<?php echo $childAction === '#' ? '#' : Url::action($childAction); ?>" class="nav-link<?php echo $childActive; ?>">
                                    <i class="nav-icon <?php echo htmlspecialchars($childIcon); ?>"></i>
                                    <p><?php echo htmlspecialchars($childTitle); ?></p>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <a href="<?php echo $groupAction === '#' ? '#' : Url::action($groupAction); ?>" class="<?php echo $linkClass; ?>">
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
