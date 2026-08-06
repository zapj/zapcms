<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 下午3:27
 * @lastModified 2023/12/27 下午3:27
 *
 */

use zap\cms\Asset;
use zap\facades\Url;

$this->layout('layouts/common');

$totalSize = 0;
foreach ($files as $f) {
    $totalSize += $f['size'];
}
?>

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex justify-content-end gap-2">
            <a href="<?php echo Url::action('System@database'); ?>" class="btn btn-outline-secondary">
                <i class="fa fa-arrow-left me-1"></i>返回数据库管理
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title flex-grow-1">
                    <i class="fa fa-history card-header-icon text-primary"></i> 备份文件列表
                    <?php if (count($files) > 0): ?>
                    <span class="badge text-bg-primary ms-2">共 <?php echo count($files); ?> 个备份</span>
                    <small class="text-muted ms-2">总占用 <?php echo \zap\util\Fmt::ByteToHuman($totalSize); ?></small>
                    <?php endif; ?>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-lte-toggle="card-collapse" aria-label="折叠">
                        <i data-lte-icon="expand" class="fa fa-plus"></i>
                        <i data-lte-icon="collapse" class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($files)): ?>
                <div class="text-center py-5">
                    <i class="fa fa-inbox text-muted" style="font-size: 3rem; display: block;"></i>
                    <p class="text-muted mt-3 mb-0">暂无备份文件</p>
                    <a href="<?php echo Url::action('System@database'); ?>" class="btn btn-primary mt-2">
                        <i class="fa fa-cloud-upload-alt me-1"></i>去备份
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>文件名</th>
                                <th class="text-end">文件大小</th>
                                <th>备份时间</th>
                                <th class="text-center" style="width: 160px;">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $idx => $file): ?>
                            <tr>
                                <td class="text-muted"><?php echo $idx + 1; ?></td>
                                <td>
                                    <i class="fa fa-file-code me-2 text-info"></i>
                                    <code><?php echo htmlspecialchars($file['name']); ?></code>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold"><?php echo \zap\util\Fmt::ByteToHuman($file['size']); ?></span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <i class="fa fa-calendar me-1"></i>
                                        <?php echo date('Y-m-d H:i:s', $file['mtime']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?php echo Url::action('System@backupDownload', null, [$file['name']]); ?>"
                                           class="btn btn-outline-primary" title="下载" download>
                                            <i class="fa fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger"
                                                onclick="deleteBackup('<?php echo addslashes($file['name']); ?>')"
                                                title="删除">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <?php if (count($files) > 0): ?>
            <div class="card-footer text-muted">
                <i class="fa fa-folder-open me-1"></i>
                备份目录：<code><?php echo var_path('backups/sql'); ?></code>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function deleteBackup(filename) {
    if (!confirm('确定要删除备份文件「' + filename + '」吗？\n此操作不可撤销！')) {
        return;
    }
    const load = Zap.loading('正在删除...');
    $.ajax({
        url: '<?php echo Url::action('System@backupDelete'); ?>',
        method: 'post',
        data: {filename: filename},
        success: function (data) {
            if (data.code === 0) {
                ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center});
                setTimeout(function () {
                    window.location.reload();
                }, 800);
            } else {
                ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
            }
        },
        error: function () {
            ZapToast.alert('请求失败，请稍后重试', {bgColor: bgDanger, position: Toast_Pos_Center});
        }
    }).always(function () {
        load.dispose();
    });
}
</script>
