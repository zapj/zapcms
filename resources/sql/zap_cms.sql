-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-10-20 21:34:17
-- 服务器版本： 5.7.26
-- PHP 版本： 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `zap_cms`
--

-- --------------------------------------------------------

--
-- 表的结构 `zap_admin`
--

CREATE TABLE `zap_admin` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `last_ip` varchar(255) DEFAULT NULL,
  `last_access_time` int(11) DEFAULT NULL,
  `status` varchar(32) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_admin`
--

INSERT INTO `zap_admin` (`id`, `username`, `password`, `full_name`, `phone_number`, `email`, `avatar_url`, `last_ip`, `last_access_time`, `status`, `updated_at`, `created_at`) VALUES
(1, 'admin', '$2y$10$2ilnde/EUAD.hq8QSkusqeh2UKQAKT6KibISwOzYBOXeQuVKd09aC', NULL, NULL, NULL, NULL, '127.0.0.1', 1697702717, 'activated', 1695192655, 1693297157);

-- --------------------------------------------------------

--
-- 表的结构 `zap_admin_logs`
--

CREATE TABLE `zap_admin_logs` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `username` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `ipaddress` varchar(255) NOT NULL,
  `request_url` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `request_time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `zap_admin_menu`
--

CREATE TABLE `zap_admin_menu` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `pid` int(11) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `level` int(11) DEFAULT '0',
  `icon` varchar(255) DEFAULT NULL,
  `link_to` varchar(255) DEFAULT NULL,
  `link_target` varchar(64) DEFAULT NULL,
  `link_type` varchar(32) DEFAULT 'action',
  `active_rule` varchar(255) DEFAULT NULL,
  `show_position` varchar(32) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_admin_menu`
--

INSERT INTO `zap_admin_menu` (`id`, `title`, `pid`, `path`, `level`, `icon`, `link_to`, `link_target`, `link_type`, `active_rule`, `show_position`, `sort_order`, `updated_at`, `created_at`) VALUES
(1, '内容管理', 0, ',1,', 1, 'fa fa-cube', 'Node', '_self', 'action', '(node/.*)', '1', 1, 1697786443, 1694683755),
(2, '栏目', 0, ',2,', 1, 'fa fa-square-poll-horizontal', 'Catalog', '_self', 'action', 'catalog/.*', '1', 2, 1694684638, 1694684638),
(3, '系统管理', 0, ',3,', 1, 'fa fa-gear', '', '_self', 'action', '(admin-menu/.*|system/.*|user/.*)', '1', 4, 1694684685, 1694684685),
(4, '基础设置', 3, ',3,4,', 2, 'fa-solid fa-angle-right', 'System@settings', '_self', 'action', '(system/.*)', '1,2', 0, 1694684704, 1694684704),
(5, '系统菜单设置', 3, ',3,5,', 2, 'fa-solid fa-angle-right', 'AdminMenu', '_self', 'action', '(admin-menu/.*|system/.*)', '1,2', 1, 1694684714, 1694684714),
(7, '主题', 0, ',7,', 1, 'fa-solid fa-wand-magic-sparkles', 'Theme', '_self', 'action', '(theme/.*)', '1', 3, 1697790328, 1697700805),
(8, '用户管理', 3, ',38,', 2, 'fa-solid fa-chevron-right', 'User', '_self', 'action', '(user/.*)', '1', 2, 1697700876, 1697700876);

-- --------------------------------------------------------

--
-- 表的结构 `zap_admin_roles`
--

CREATE TABLE `zap_admin_roles` (
  `admin_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assignment_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `zap_catalog`
--

CREATE TABLE `zap_catalog` (
  `id` int(11) NOT NULL,
  `seo_name` varchar(192) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `pid` int(11) NOT NULL,
  `path` varchar(255) NOT NULL,
  `level` int(11) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `icon` varchar(255) DEFAULT NULL,
  `thumb_url` varchar(255) DEFAULT NULL,
  `link_to` varchar(255) DEFAULT NULL,
  `link_target` varchar(32) DEFAULT NULL,
  `show_position` varchar(32) DEFAULT NULL,
  `node_type` varchar(64) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_catalog`
--

INSERT INTO `zap_catalog` (`id`, `seo_name`, `title`, `content`, `pid`, `path`, `level`, `sort_order`, `icon`, `thumb_url`, `link_to`, `link_target`, `show_position`, `node_type`, `created_at`) VALUES
(1, 'about-us', '关于我们', NULL, 0, ',1,', 1, 0, NULL, NULL, NULL, NULL, '1,2,3', 'page', 1695221265),
(2, 'product', '产品中心', NULL, 0, ',2,', 1, 0, NULL, NULL, NULL, NULL, '1,2', 'product', 1695221280),
(3, 'computer', '电脑', NULL, 2, ',2,3,', 2, 0, NULL, NULL, NULL, NULL, '1', 'product', 1695221301),
(4, 'phone', '手机', NULL, 2, ',2,4,', 2, 0, NULL, NULL, NULL, NULL, '1', 'product', 1695221318),
(5, 'news', '新闻中心', NULL, 0, ',5,', 1, 0, NULL, NULL, NULL, NULL, '1', 'article', 1695221346),
(6, 'company-news', '公司新闻', NULL, 5, ',5,6,', 2, 0, NULL, NULL, NULL, NULL, '1', 'article', 1695221346),
(7, 'industry-news', '行业新闻', NULL, 5, ',5,7,', 2, 0, NULL, NULL, NULL, NULL, '1', 'article', 1695221466),
(8, 'faq', '常见问题', NULL, 0, ',8,', 1, 0, NULL, NULL, NULL, NULL, '1', 'faq', 1695996567);

-- --------------------------------------------------------

--
-- 表的结构 `zap_media`
--

CREATE TABLE `zap_media` (
  `id` int(11) NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `modified_at` datetime NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `original_filename` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `copyright` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `zap_node`
--

CREATE TABLE `zap_node` (
  `id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `node_type` varchar(64) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) DEFAULT NULL,
  `content` text,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `hits` int(11) DEFAULT '0' COMMENT '点击数',
  `sort_order` int(11) DEFAULT '0',
  `status` varchar(64) DEFAULT NULL,
  `pub_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `add_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_node`
--

INSERT INTO `zap_node` (`id`, `author_id`, `node_type`, `title`, `slug`, `content`, `keywords`, `description`, `image`, `hits`, `sort_order`, `status`, `pub_time`, `update_time`, `add_time`) VALUES
(8, 1, 'article', '测试新闻系统', NULL, '<p>测试新闻系统<br></p>', '', '', NULL, 0, 0, 'publish', 1694660587, 1695201736, 1694660590),
(9, 1, 'article', '测试新闻系统', NULL, '<p>测试新闻系统</p>', '', '', NULL, 1000, 0, 'publish', 1695438223, 1697697694, 1694660626),
(10, 1, 'article', 'ZAP CMS v1.0.0 正式发布', NULL, '<p>zap cms v1.0.0 正式发布<br>zap cms v1.0.0 正式发布<br>zap cms v1.0.0 正式发布<br>zap cms v1.0.0 正式发布<br></p>', '', '', NULL, 0, 0, 'publish', 1695275304, 1697697611, 1695275332),
(12, 1, 'article', '测试新闻系统', NULL, '', '', '', NULL, 0, 0, 'publish', 1695281370, 1695281380, 1695281380),
(13, 1, 'article', '测试新闻系统', NULL, '<pre class=\"\">asasasas</pre><pre class=\"\" style=\"font-family: var(--bs-font-monospace); line-height: 1.42857;\">asasasasasasasasasasasasasasasas</pre>', '', '', NULL, 1000, 0, 'publish', 1695454566, 1697697642, 1695281777),
(14, 1, 'article', '公司新闻 1.0', NULL, '', '', '', NULL, 0, 0, 'publish', 1695295910, 1695295918, 1695295918),
(15, 1, 'article', '行业新闻 1.3', NULL, '', '', '', NULL, 0, 0, 'publish', 1695296226, 1697697657, 1695296241),
(16, 1, 'product', 'iPhone 15', NULL, '<p>手机</p>', '', '', NULL, 0, 0, 'publish', 1695996072, 1697699848, 1695996087),
(17, 1, 'product', 'Macbook Pro 15', NULL, '<p>Macbook Pro 15<br></p>', '', '', NULL, 0, 0, 'publish', 1695996209, 1697697825, 1695996226),
(18, 1, 'faq', '为什么选择我们', NULL, '<p>测试</p>', '', '', NULL, 0, 0, 'publish', 1695997209, 1697699870, 1695997222),
(19, 1, 'page', '关于我们', NULL, '<p>关于我们关于我们关于我们关于我们关于我们关于我们关于我们关于我们<br></p>', '关于我们', '', NULL, 0, 0, 'publish', 1696574778, 1697795906, 1696574791),
(20, 1, 'faq', 'FAQ1', NULL, '<p>FAQ1<br></p>', '', '', NULL, 0, 0, 'publish', 1697008822, 1697699865, 1697008831);

-- --------------------------------------------------------

--
-- 表的结构 `zap_node_meta`
--

CREATE TABLE `zap_node_meta` (
  `id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `meta_name` varchar(255) DEFAULT NULL,
  `meta_value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `zap_node_relation`
--

CREATE TABLE `zap_node_relation` (
  `catalog_id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_node_relation`
--

INSERT INTO `zap_node_relation` (`catalog_id`, `node_id`) VALUES
(1, 19),
(2, 16),
(2, 17),
(3, 17),
(4, 16),
(5, 9),
(5, 10),
(5, 13),
(5, 15),
(6, 10),
(6, 13),
(7, 9),
(7, 15),
(8, 18),
(8, 20);

-- --------------------------------------------------------

--
-- 表的结构 `zap_node_types`
--

CREATE TABLE `zap_node_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text COMMENT '简介',
  `node_type` varchar(255) DEFAULT NULL,
  `version` varchar(32) DEFAULT '0.0.0',
  `sort_order` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_node_types`
--

INSERT INTO `zap_node_types` (`type_id`, `type_name`, `title`, `description`, `node_type`, `version`, `sort_order`, `status`, `updated_at`, `created_at`) VALUES
(1, 'article', '文章', '文章、新闻', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650),
(2, 'product', '产品', '简单企业产品展示', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650),
(3, 'page', '单页', '单页', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650),
(4, 'faq', 'FAQ', '常见问题', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650),
(5, 'link-url', '链接', '链接地址', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650),
(6, 'comments', '留言板', '管理客户留言及文章评论', 'zap\\node\\AbstractNodeType', '1.0.0', 0, 1, 1694153650, 1694153650);

-- --------------------------------------------------------

--
-- 表的结构 `zap_node_type_field`
--

CREATE TABLE `zap_node_type_field` (
  `field_id` int(11) NOT NULL,
  `node_type_id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_label` varchar(255) NOT NULL,
  `field_value` text,
  `sort_order` int(11) DEFAULT '0',
  `type` varchar(64) DEFAULT NULL,
  `placeholder` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `zap_options`
--

CREATE TABLE `zap_options` (
  `id` int(11) NOT NULL,
  `option_name` varchar(255) NOT NULL,
  `option_value` text,
  `sort_order` int(11) DEFAULT '0',
  `autoload` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_options`
--

INSERT INTO `zap_options` (`id`, `option_name`, `option_value`, `sort_order`, `autoload`) VALUES
(11, 'website.title', 'ZAP CMS', 0, 1),
(12, 'website.slogan', 'OpenSource', 0, 1),
(13, 'website.keywords', 'ZAP,CMS,PHP OpenSource CMS', 0, 1),
(14, 'website.description', '', 0, 1),
(15, 'website.icp', '浙ICP备0000000000号', 0, 1),
(16, 'website.copyright', 'ZAPCMS 版权所有', 0, 1),
(17, 'website.address', '公司详细地址', 0, 1),
(18, 'website.tel', '057388888888', 0, 1),
(19, 'website.head_script', '', 0, 1),
(20, 'website.foot_script', '', 0, 1);

-- --------------------------------------------------------

--
-- 表的结构 `zap_permissions`
--

CREATE TABLE `zap_permissions` (
  `perm_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `pid` int(11) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  `level` int(11) DEFAULT '0',
  `slug` varchar(255) DEFAULT NULL,
  `extras` text,
  `description` varchar(255) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_permissions`
--

INSERT INTO `zap_permissions` (`perm_id`, `title`, `pid`, `path`, `level`, `slug`, `extras`, `description`, `updated_at`, `created_at`) VALUES
(8, '用户管理', 0, ',8,', 1, 'user.index', NULL, '', 1697804460, 1697804443),
(10, '添加用户', 8, ',8,10,', 2, 'user.add', NULL, '', 1697805259, 1697805259),
(11, '修改用户', 0, ',11,', 1, 'user.edit', NULL, '', 1697805548, 1697805548);

-- --------------------------------------------------------

--
-- 表的结构 `zap_roles`
--

CREATE TABLE `zap_roles` (
  `role_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `status` int(11) DEFAULT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_roles`
--

INSERT INTO `zap_roles` (`role_id`, `name`, `status`, `updated_at`, `created_at`) VALUES
(1, '超级管理员', NULL, 1697780789, 1697774895);

-- --------------------------------------------------------

--
-- 表的结构 `zap_roles_permissions`
--

CREATE TABLE `zap_roles_permissions` (
  `role_id` int(11) NOT NULL,
  `perm_id` int(11) NOT NULL,
  `assignment_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `zap_admin`
--
ALTER TABLE `zap_admin`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `zap_admin_logs`
--
ALTER TABLE `zap_admin_logs`
  ADD KEY `uid` (`uid`);

--
-- 表的索引 `zap_admin_menu`
--
ALTER TABLE `zap_admin_menu`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `zap_admin_roles`
--
ALTER TABLE `zap_admin_roles`
  ADD PRIMARY KEY (`admin_id`,`role_id`);

--
-- 表的索引 `zap_catalog`
--
ALTER TABLE `zap_catalog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seo_name` (`seo_name`);

--
-- 表的索引 `zap_node`
--
ALTER TABLE `zap_node`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `status` (`status`),
  ADD KEY `node_type` (`node_type`) USING BTREE;

--
-- 表的索引 `zap_node_meta`
--
ALTER TABLE `zap_node_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`node_id`);

--
-- 表的索引 `zap_node_relation`
--
ALTER TABLE `zap_node_relation`
  ADD PRIMARY KEY (`catalog_id`,`node_id`),
  ADD KEY `catalog_id` (`catalog_id`),
  ADD KEY `node_id` (`node_id`);

--
-- 表的索引 `zap_node_types`
--
ALTER TABLE `zap_node_types`
  ADD PRIMARY KEY (`type_id`),
  ADD KEY `type_name` (`type_name`);

--
-- 表的索引 `zap_node_type_field`
--
ALTER TABLE `zap_node_type_field`
  ADD PRIMARY KEY (`field_id`),
  ADD KEY `node_type_id` (`node_type_id`);

--
-- 表的索引 `zap_options`
--
ALTER TABLE `zap_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_name` (`option_name`) USING BTREE;

--
-- 表的索引 `zap_permissions`
--
ALTER TABLE `zap_permissions`
  ADD PRIMARY KEY (`perm_id`);

--
-- 表的索引 `zap_roles`
--
ALTER TABLE `zap_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- 表的索引 `zap_roles_permissions`
--
ALTER TABLE `zap_roles_permissions`
  ADD PRIMARY KEY (`role_id`,`perm_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `zap_admin`
--
ALTER TABLE `zap_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `zap_admin_menu`
--
ALTER TABLE `zap_admin_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- 使用表AUTO_INCREMENT `zap_catalog`
--
ALTER TABLE `zap_catalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- 使用表AUTO_INCREMENT `zap_node`
--
ALTER TABLE `zap_node`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `zap_node_meta`
--
ALTER TABLE `zap_node_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `zap_node_types`
--
ALTER TABLE `zap_node_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- 使用表AUTO_INCREMENT `zap_node_type_field`
--
ALTER TABLE `zap_node_type_field`
  MODIFY `field_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `zap_options`
--
ALTER TABLE `zap_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- 使用表AUTO_INCREMENT `zap_permissions`
--
ALTER TABLE `zap_permissions`
  MODIFY `perm_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- 使用表AUTO_INCREMENT `zap_roles`
--
ALTER TABLE `zap_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
