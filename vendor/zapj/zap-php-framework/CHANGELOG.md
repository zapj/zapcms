# Changelog

所有重要的变更都会记录在此文件中。

格式基于 [Keep a Changelog](https://keepachangelog.com/zh-CN/1.1.0/)，
版本号遵循 [语义化版本](https://semver.org/lang/zh-CN/)。

## [1.0.6] - 2026-08-13

### 新增

- 子目录部署支持：自动探测脚本目录前缀（如 `/zapcms`），`url()` / `route()` 生成的相对链接自动前置子目录前缀，路由分发时自动剥离；同一份代码可同时通过 `http://host/zapcms/xxx` 与 `http://zapcms.local/xxx` 访问
- `TableSchema::addIndex()` 支持联合索引：`addIndex('idx', ['a', 'b'])` 或 `addIndex('idx', 'a', 'b')`
- `AlertTable::batchInsert()` 新增 `$ignore` 参数：`batchInsert($rows, true)` 跳过主键冲突（MySQL 生成 `INSERT IGNORE`，SQLite 生成 `INSERT OR IGNORE`）
- `RedisCache` 支持 `password` / `database` 配置：连接后自动执行 AUTH 认证与 `select` 选库

### 变更

- 移除 `Z_ADMIN_PREFIX` 常量，后台管理前缀改为读取 `config('admin.prefix')`（默认 `z-admin`），判断逻辑统一为 `adminPrefix()` / `isAdminContext()`

### 修复

- 修复 `AlertTable` 批量插入在 SQLite/PGSQL 下字符串转义错误（`addslashes` 会重复转义反斜杠），改为按驱动转义：MySQL 用 `addslashes`，SQLite/PGSQL 单引号翻倍
- 修复路由文件加载路径，改用 `basePath` 定位 `config/route.php`
- 修复 `UrlHelper` 子目录部署下 `base()` / `current()` / `controller()` / `method()` 路径解析偏差

## [1.0.5] - 2026-08-13

### 新增

- `zap\html\Html::doctype()`：文档类型声明，支持 HTML5 / HTML 4.01 / XHTML 1.0 / XHTML 1.1
- 全局 `e()` / `esc()` 函数：模板中的 HTML 转义输出，默认转义 `ENT_QUOTES`
- `DB::fetch()` / `DB::fetchAll()`：单行 / 多行查询，支持通过 `setFetchModel()` 或参数指定模型类自动填充
- `DB::raw()`：SQL 原生表达式，如 `DB::raw('hits+1')`，用于字段自增等场景
- URL 后缀支持：配置 `config('config.suffix')`（如 `.html`）后，`url()` / `route()` 生成的链接自动追加后缀，请求分发时自动剥离

### 修复

- `route()` 函数调用不存在的方法 `Router::getRouteUrl()`，改为 `Router::url()`
- `zap\App` 的 `ArrayAccess` 方法签名与 PHP 8.0+ 不兼容（intelephense P1038）
- README 中应用启动示例错误（不存在 `environment()` / `withRoutes()` 等方法）

### 文档

- 更新数据库、路由、辅助函数、视图、安全、HTML 构建器等相关文档

## [1.0.1] - 2026-08-11

### 新增

- 初始发布：路由、控制器、视图、数据库（ZPDO）、缓存、日志、会话、中间件等核心模块
- `zap\crypto`：加密解密工具
- `zap\password`：密码哈希与随机数生成
- HTML 构建器：`zap\html\Html` / `zap\html\Element`

## 说明

历史提交已合并为单个提交，早期增量版本（1.0.1 ~ 1.0.4）的详细变更不再逐一列出。
