-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-09-14 17:52:43
-- 服务器版本： 5.7.26
-- PHP 版本： 7.3.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
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
  `fullname` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `last_ip` varchar(255) DEFAULT NULL,
  `last_access_time` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_admin`
--

INSERT INTO `zap_admin` (`id`, `username`, `password`, `fullname`, `phone_number`, `email`, `avatar_url`, `last_ip`, `last_access_time`, `created_at`) VALUES
(1, 'admin', '$2y$10$joUwD80AFeD7v1ikTuWmlOLwHrBCOTj5fQrKNKFwB/o1.R61mWbem', NULL, NULL, NULL, NULL, '127.0.0.1', 1694506609, 1693297157);

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
  `show_position` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_admin_menu`
--

INSERT INTO `zap_admin_menu` (`id`, `title`, `pid`, `path`, `level`, `icon`, `link_to`, `link_target`, `link_type`, `active_rule`, `show_position`, `sort_order`, `updated_at`, `created_at`) VALUES
(1, '内容管理', 0, '1', 1, 'fa fa-circle-notch', NULL, NULL, 'action', NULL, 1, 1, 1694683755, 1694683755),
(2, '栏目', 0, '2', 1, 'fa fa-circle-notch', 'Catalog', '', 'action', '', 1, 2, 1694684638, 1694684638),
(3, '设置', 0, '3', 1, 'fa fa-circle-notch', '', '_self', 'action', '', 1, 3, 1694684685, 1694684685),
(4, '基础设置', 3, '3,4', 2, 'fa fa-circle-notch', '', '_self', 'action', '', 0, 0, 1694684704, 1694684704),
(5, '系统菜单设置', 3, '3,5', 2, 'fa fa-circle-notch', '', '_self', 'action', '', 0, 0, 1694684714, 1694684714);

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
  `show_position` int(10) DEFAULT NULL,
  `node_type` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_catalog`
--

INSERT INTO `zap_catalog` (`id`, `seo_name`, `title`, `content`, `pid`, `path`, `level`, `sort_order`, `icon`, `thumb_url`, `link_to`, `link_target`, `show_position`, `node_type`, `created_at`) VALUES
(24, NULL, 'JAVA', NULL, 23, '23,24', 2, 0, NULL, NULL, NULL, NULL, 31, 1, 1694677733),
(23, NULL, '编程语言', NULL, 0, '23', 1, 0, NULL, NULL, NULL, NULL, 31, 1, 1694677714),
(25, NULL, 'PHP', NULL, 23, '23,25', 2, 1, NULL, NULL, NULL, NULL, 31, 1, 1694677742),
(26, NULL, '产品中心', NULL, 0, '26', 1, 0, NULL, NULL, NULL, NULL, 31, 1, 1694677774),
(27, NULL, '吹风机', NULL, 26, '26,27', 2, 0, NULL, NULL, NULL, NULL, 31, 1, 1694677791),
(28, NULL, '洗衣机', NULL, 26, '26,28', 2, 0, NULL, NULL, NULL, NULL, 31, 1, 1694677799);

-- --------------------------------------------------------

--
-- 表的结构 `zap_node`
--

CREATE TABLE `zap_node` (
  `id` int(11) NOT NULL,
  `node_type` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `seo_name` varchar(255) DEFAULT NULL,
  `content` text,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `hits` int(11) DEFAULT '0' COMMENT '点击数',
  `sort_order` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  `pub_time` int(11) DEFAULT NULL,
  `update_time` int(11) DEFAULT NULL,
  `add_time` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_node`
--

INSERT INTO `zap_node` (`id`, `node_type`, `title`, `seo_name`, `content`, `keywords`, `description`, `hits`, `sort_order`, `status`, `pub_time`, `update_time`, `add_time`) VALUES
(2, 1, '测试新闻模块', NULL, '<p>测试新闻模块</p><p><img src=\"/storage/images/a5e00132373a7031000fd987a3c9f87b.png\" style=\"width: 1241.33px;\"></p>', '', '', 1000, 0, 1, 1694507942, 1694660069, NULL),
(8, 1, '测试新闻系统', NULL, '<p>测试新闻系统<br></p>', '', '', 0, 0, 1, 1694660587, 1694660590, 1694660590),
(9, 1, '测试新闻系统1', NULL, '<p>测试新闻系统</p><p><img src=\"/storage/images/2723d092b63885e0d7c260cc007e8b9d.png\" style=\"width: 331px;\"><br></p>', '', '', 1000, 0, 1, 1695438223, 1694672674, 1694660626);

-- --------------------------------------------------------

--
-- 表的结构 `zap_node_meta`
--

CREATE TABLE `zap_node_meta` (
  `id` int(11) NOT NULL,
  `node_id` int(11) NOT NULL,
  `meta_name` varchar(255) DEFAULT NULL,
  `meta_value` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `zap_node_relationships`
--

CREATE TABLE `zap_node_relationships` (
  `content_type` int(11) NOT NULL,
  `object_id` int(11) NOT NULL,
  `catalog_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `zap_node_types`
--

CREATE TABLE `zap_node_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text COMMENT '简介',
  `module_name` varchar(255) DEFAULT NULL,
  `modue_version` varchar(32) DEFAULT '0.0.0',
  `module_type` varchar(32) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `updated_at` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_node_types`
--

INSERT INTO `zap_node_types` (`id`, `name`, `description`, `module_name`, `modue_version`, `module_type`, `sort_order`, `updated_at`, `created_at`) VALUES
(1, '新闻', NULL, 'news', '1.0.0', 'zap', 0, NULL, 1694153650),
(2, '产品', NULL, 'product', '1.0.0', 'zap', 0, NULL, 1694153650),
(3, '单页', NULL, 'page', '1.0.0', 'zap', 0, NULL, 1694153650),
(4, '文档', NULL, 'document', '1.0.0', 'zap', 0, NULL, 1694153650),
(5, '链接', '链接地址', 'linkurl', '1.0.0', 'zap', 0, NULL, 1694153650);

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
(103, 'test', 'test2023-09-14 15:00:36', 0, 0),
(2, 'web.sitename', 'ZAP WEB', 0, 0),
(18, 'web.site_slogan', 'ZAP WEBSITE 1222', 0, 0);

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
  ADD KEY `content_type` (`node_type`);

--
-- 表的索引 `zap_node_meta`
--
ALTER TABLE `zap_node_meta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `content_id` (`node_id`);

--
-- 表的索引 `zap_node_types`
--
ALTER TABLE `zap_node_types`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `zap_options`
--
ALTER TABLE `zap_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `option_name` (`option_name`) USING BTREE;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `zap_catalog`
--
ALTER TABLE `zap_catalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- 使用表AUTO_INCREMENT `zap_node`
--
ALTER TABLE `zap_node`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- 使用表AUTO_INCREMENT `zap_node_meta`
--
ALTER TABLE `zap_node_meta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `zap_node_types`
--
ALTER TABLE `zap_node_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- 使用表AUTO_INCREMENT `zap_options`
--
ALTER TABLE `zap_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
