<?php

use zap\cms\NodeType;

$page_title = '控制台';
$this->layout('layouts/common');
?>

<!--begin::Stats Row - Small Boxes-->
<div class="row">
    <?php
    $box_colors = ['text-bg-primary', 'text-bg-success', 'text-bg-warning', 'text-bg-info', 'text-bg-danger', 'text-bg-secondary'];
    $box_icons  = [
        'bi bi-file-earmark-text',
        'bi bi-grid-3x3-gap',
        'bi bi-camera',
        'bi bi-collection',
        'bi bi-images',
        'bi bi-folder2',
    ];
    $color_idx  = 0;
    $icon_idx   = 0;
    foreach ($node_types_statistics as $type => $count):
        $color = $box_colors[$color_idx % count($box_colors)];
        $icon  = $box_icons[$icon_idx % count($box_icons)];
        $title = NodeType::getTitle($type);
        $color_idx++;
        $icon_idx++;
    ?>
    <div class="col-lg-3 col-6">
        <div class="small-box <?php echo $color; ?>">
            <div class="inner">
                <h3><?php echo $count; ?></h3>
                <p><?php echo $title; ?></p>
            </div>
            <i class="<?php echo $icon; ?> small-box-icon fs-1"></i>
            <a href="<?php echo url_action("Node@{$type}"); ?>" class="small-box-footer link-light link-underline-opacity-0 link-underline-opacity-50-hover">
                管理 <?php echo $title; ?> <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<!--end::Stats Row-->

<!--begin::Main Content Row-->
<div class="row">
    <!--begin::Pages Section-->
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-file-earmark card-header-icon text-primary"></i> 单页管理
                </h3>
                <div class="card-tools">
                    <span class="badge text-bg-secondary"><?php echo count($pages); ?> 个页面</span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pages)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    暂无单页内容
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">标题</th>
                                <th>状态</th>
                                <th class="text-end pe-3">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $page): ?>
                            <tr>
                                <td class="ps-3">
                                    <a href="<?php echo url_action("Node@page/edit/{$page['id']}"); ?>" class="text-decoration-none">
                                        <i class="bi bi-file-text text-success me-1"></i>
                                        <?php echo htmlspecialchars($page['title']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!empty($page['status'])): ?>
                                    <span class="badge text-bg-success">已发布</span>
                                    <?php else: ?>
                                    <span class="badge text-bg-warning">草稿</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?php echo url_action("Node@page/edit/{$page['id']}"); ?>"
                                       class="btn btn-sm btn-outline-primary"
                                       title="编辑">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!--end::Pages Section-->

    <!--begin::Website Info-->
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-info-circle card-header-icon text-info"></i> 网站信息
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-translate me-2 text-muted"></i>系统语言</span>
                        <span class="fw-semibold"><?php echo req()->language(); ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-palette me-2 text-muted"></i>站点模版</span>
                        <span class="fw-semibold"><?php echo option('website.theme', 'basic'); ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-upload me-2 text-muted"></i>上传限制</span>
                        <span class="fw-semibold"><?php echo ini_get('upload_max_filesize'); ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-memory me-2 text-muted"></i>内存限制</span>
                        <span class="fw-semibold"><?php echo ini_get('memory_limit'); ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-cpu me-2 text-muted"></i>PHP 版本</span>
                        <span class="fw-semibold"><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-hdd-stack me-2 text-muted"></i>数据库</span>
                        <span class="fw-semibold"><?php echo \zap\DB::connection()->getAttribute(PDO::ATTR_DRIVER_NAME); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Website Info-->
</div>
<!--end::Main Content Row-->

<!--begin::System Info-->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="bi bi-server card-header-icon text-secondary"></i> 系统信息
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="折叠">
                        <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                        <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <tbody>
                        <tr>
                            <td class="fw-semibold" style="width: 160px;">
                                <i class="bi bi-box-seam me-2 text-primary"></i>ZapCMS 版本
                            </td>
                            <td>v<?php echo ZAP_CMS_VERSION . '-' . ZAP_CMS_RELEASE_DATE; ?></td>
                            <td class="fw-semibold" style="width: 120px;">
                                <i class="bi bi-code-slash me-2 text-success"></i>PHP 版本
                            </td>
                            <td><?php echo PHP_VERSION . ' (' . php_sapi_name() . ')'; ?></td>
                        </tr>
                        <tr>
                            <?php $driver = \zap\DB::connection()->getAttribute(PDO::ATTR_DRIVER_NAME); ?>
                            <td class="fw-semibold">
                                <i class="bi bi-database me-2 text-warning"></i><?php echo $driver; ?>
                            </td>
                            <td>v<?php
                                if ($driver == 'mysql') {
                                    echo \zap\DB::value("SELECT VERSION()");
                                } elseif ($driver == 'sqlite') {
                                    $dbh = new \PDO('sqlite::memory:');
                                    if ($dbh) {
                                        echo $dbh->query('select sqlite_version()')->fetchColumn(0);
                                        $dbh = null;
                                    } else {
                                        echo '当前环境不支持sqlite3';
                                    }
                                }
                            ?></td>
                            <td class="fw-semibold">
                                <i class="bi bi-shield-lock me-2 text-info"></i>OpenSSL
                            </td>
                            <td><?php echo defined("OPENSSL_VERSION_TEXT") ? OPENSSL_VERSION_TEXT : '不支持'; ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">
                                <i class="bi bi-image me-2 text-danger"></i>PHP GD
                            </td>
                            <td><?php $gdInfo = gd_info(); echo current($gdInfo); ?></td>
                            <td class="fw-semibold">
                                <i class="bi bi-plug me-2 text-secondary"></i>PDO 驱动
                            </td>
                            <td><?php echo join(' / ', PDO::getAvailableDrivers()); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">
                                <i class="bi bi-globe2 me-2 text-primary"></i>Web Server
                            </td>
                            <td colspan="3"><?php echo \zap\http\Request::server('SERVER_SOFTWARE'); ?></td>
                        </tr>
                        <tr>
                            <td class="fw-semibold">
                                <i class="bi bi-pc-display me-2 text-success"></i>操作系统
                            </td>
                            <td colspan="3"><?php echo php_uname(); ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::System Info-->
