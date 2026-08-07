-- ================================================================
-- ZAP CMS 菜单更新：系统管理 → 设置，新增 Sitemap 和固定链接设置
-- 执行前请备份数据库
-- ================================================================

-- 1. 更新父级菜单名称：系统管理 → 设置
UPDATE `admin_menu` SET `title` = '设置' WHERE `id` = 3;

-- 2. 更新"基础设置"的 active_rule（精确匹配，避免与其他子页面冲突）
UPDATE `admin_menu` SET `active_rule` = '(system/settings.*)' WHERE `id` = 4;

-- 3. 更新"系统菜单设置"的 active_rule（精确匹配）
UPDATE `admin_menu` SET `active_rule` = '(admin-menu/.*)' WHERE `id` = 5;

-- 4. 修复"用户管理"的 parents 路径（原始数据可能有误）
UPDATE `admin_menu` SET `parents` = '3,8,' WHERE `id` = 8;

-- 5. 新增 Sitemap 菜单项
INSERT INTO `admin_menu` (`id`, `title`, `pid`, `parents`, `level`, `icon`, `url`, `target`, `url_type`, `active_rule`, `roles`, `sort_order`, `updated_at`, `created_at`) VALUES
(11, 'Sitemap', 3, '3,11,', 2, 'fa-solid fa-sitemap', 'System@sitemap', '_self', 'action', '(system/sitemap.*)', '1,2', 4, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 6. 新增固定链接设置菜单项
INSERT INTO `admin_menu` (`id`, `title`, `pid`, `parents`, `level`, `icon`, `url`, `target`, `url_type`, `active_rule`, `roles`, `sort_order`, `updated_at`, `created_at`) VALUES
(12, '固定链接设置', 3, '3,12,', 2, 'fa-solid fa-link', 'System@permalink', '_self', 'action', '(system/permalink.*)', '1,2', 5, UNIX_TIMESTAMP(), UNIX_TIMESTAMP());

-- 7. 为新菜单项添加超级管理员权限
INSERT INTO `roles_permissions` (`role_id`, `perm_key`, `extras`, `assignment_time`) VALUES
(1, 'admin_menu_11', '', UNIX_TIMESTAMP()),
(1, 'admin_menu_12', '', UNIX_TIMESTAMP());

-- 8. 新增固定链接默认配置项
INSERT INTO `options` (`id`, `option_name`, `option_value`, `sort_order`, `autoload`) VALUES
(80, 'permalink.structure', '/%postname%/', 0, 1),
(81, 'permalink.catalog_prefix', 'catalog', 0, 1)
ON DUPLICATE KEY UPDATE `option_value` = VALUES(`option_value`);
