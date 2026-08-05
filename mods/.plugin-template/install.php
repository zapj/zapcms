<?php
/**
 * 插件安装脚本
 * 插件安装时自动执行
 *
 * 此文件中可以使用以下变量：
 * - $name: 插件名称
 * - DB:: 数据库操作
 * - Schema:: 创建表
 */

// 创建自定义数据表
\zap\db\Schema::create('example_plugin_data', function (\zap\db\TableSchema $table) {
    $table->integer('id')->autoIncrement();
    $table->varchar('title', 255);
    $table->text('content')->nullable();
    $table->integer('created_at')->nullable()->default(0);

    $table->addPrimary('example_pk', 'id');
    $table->dropTableIfExists();
    $table->setTableEngine(\zap\db\TableSchema::ENGINE_INNODB);
});

// 添加后台菜单项
// \zap\cms\AdminMenu::moduleItem([
//     'title' => '示例插件',
//     'icon'  => 'fa-solid fa-star',
//     'url'   => '/z-admin/mod/example-plugin'
// ]);

// 添加配置项
\zap\cms\Option::add('plugin.example.config', 'default_value');
