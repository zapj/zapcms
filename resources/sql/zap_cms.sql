-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2023-09-07 11:22:40
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
  `last_ip` varchar(255) DEFAULT NULL,
  `last_access_time` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_admin`
--

INSERT INTO `zap_admin` (`id`, `username`, `password`, `fullname`, `phone_number`, `email`, `last_ip`, `last_access_time`, `created_at`) VALUES
(1, 'admin', '$2y$10$joUwD80AFeD7v1ikTuWmlOLwHrBCOTj5fQrKNKFwB/o1.R61mWbem', NULL, NULL, NULL, '127.0.0.1', 1693961989, 1693297157);

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
  `name` varchar(255) NOT NULL,
  `pid` int(11) NOT NULL,
  `path` int(11) DEFAULT NULL,
  `level` int(11) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0',
  `icon` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_admin_menu`
--

INSERT INTO `zap_admin_menu` (`id`, `name`, `pid`, `path`, `level`, `sort_order`, `icon`) VALUES
(1, '内容管理', 0, 0, 0, 0, 'fa fa-cube'),
(2, '栏目', 0, 0, 0, 0, 'fa fa-ellipsis');

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
  `content_type` int(11) DEFAULT NULL,
  `created_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_catalog`
--

INSERT INTO `zap_catalog` (`id`, `seo_name`, `title`, `content`, `pid`, `path`, `level`, `sort_order`, `icon`, `thumb_url`, `link_to`, `link_target`, `show_position`, `content_type`, `created_at`) VALUES
(16, NULL, 'JASON ZAP', NULL, 15, '0,15', 1, 0, NULL, NULL, NULL, NULL, 1, 1, 1693993433),
(15, NULL, 'PHP ZAP', NULL, 0, '0', 0, 0, NULL, NULL, NULL, NULL, 1, 1, 1693992770),
(13, NULL, 'JASON', NULL, 0, '0', 0, 4, NULL, NULL, NULL, NULL, 8, 1, 1693990927),
(14, NULL, 'PHP', NULL, 0, '0', 0, 3, NULL, NULL, NULL, NULL, 4, 1, 1693992601),
(17, NULL, 'JASON ZAP WEB', NULL, 16, '0,15,16', 2, 1, NULL, NULL, NULL, NULL, 32, 1, 1693993823),
(18, NULL, 'Jason Sun', NULL, 16, '0,15,16', 2, 0, NULL, NULL, NULL, NULL, 32, 1, 1694049236),
(19, NULL, 'Programs', NULL, 0, '0', 0, 0, NULL, NULL, NULL, NULL, 31, 1, 1694053668);

-- --------------------------------------------------------

--
-- 表的结构 `zap_category`
--

CREATE TABLE `zap_category` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `path` varchar(255) DEFAULT NULL,
  `pid` int(11) NOT NULL,
  `level` int(11) DEFAULT '0',
  `sort_order` int(11) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_category`
--

INSERT INTO `zap_category` (`id`, `name`, `path`, `pid`, `level`, `sort_order`) VALUES
(1, '编程语言', '0', 0, 0, 0),
(2, 'Spring Boot', '0,1', 1, 1, 0),
(3, 'Spring Web', '0,1', 1, 1, 0);

-- --------------------------------------------------------

--
-- 表的结构 `zap_content_types`
--

CREATE TABLE `zap_content_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text COMMENT '简介',
  `class_name` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `created_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `zap_content_types`
--

INSERT INTO `zap_content_types` (`id`, `name`, `description`, `class_name`, `sort_order`, `created_at`) VALUES
(1, '新闻', NULL, '\\zap\\models\\News', 0, NULL);

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
(35, 'test', 'test', 0, 0),
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
-- 表的索引 `zap_category`
--
ALTER TABLE `zap_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_pid` (`pid`),
  ADD KEY `category_path` (`path`);

--
-- 表的索引 `zap_content_types`
--
ALTER TABLE `zap_content_types`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- 使用表AUTO_INCREMENT `zap_catalog`
--
ALTER TABLE `zap_catalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- 使用表AUTO_INCREMENT `zap_category`
--
ALTER TABLE `zap_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `zap_content_types`
--
ALTER TABLE `zap_content_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- 使用表AUTO_INCREMENT `zap_options`
--
ALTER TABLE `zap_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
