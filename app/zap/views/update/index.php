<?php

use zap\facades\Url;

!IS_AJAX && $this->layout('layouts/common');
?>
<nav class="navbar bg-body-tertiary mb-3 rounded shadow-sm">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('Update') ?>">系统更新</a></li>
            </ol>
        </nav>
        <div class="text-end">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="checkUpdate()">
                <i class="fa-solid fa-arrows-rotate"></i> 重新检查
            </button>
        </div>
    </div>
</nav>

<main class="container zap-main">
    <!-- 系统状态卡片 -->
    <div class="row mt-3">
        <div class="col-md-4">
            <div class="card mb-3 shadow-sm <?php echo ($update_info['has_update'] ?? false) ? 'border-warning' : 'border-success' ?>">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="fa-solid fa-server"></i> 当前版本</h6>
                    <h4 class="mt-3 mb-3">
                        <span class="badge bg-primary" style="font-size: 1.2rem;">v<?php echo htmlspecialchars(ZAP_CMS_VERSION) ?></span>
                    </h4>
                    <?php if ($update_info && $update_info['has_update']): ?>
                        <div class="alert alert-warning py-2 mb-2">
                            <i class="fa-solid fa-circle-exclamation"></i> 发现新版本: <strong>v<?php echo htmlspecialchars($update_info['latest_version']) ?></strong>
                        </div>
                        <?php if ($update_info['is_critical'] ?? false): ?>
                            <span class="badge bg-danger mb-2">重要安全更新</span>
                        <?php endif; ?>
                        <button class="btn btn-warning btn-sm mt-2" onclick="showUpdateConfirm()">
                            <i class="fa-solid fa-cloud-download-alt"></i> 立即更新
                        </button>
                    <?php elseif ($update_info): ?>
                        <div class="text-success">
                            <i class="fa-solid fa-circle-check"></i> 已是最新版本
                        </div>
                    <?php else: ?>
                        <div class="text-muted">
                            <i class="fa-solid fa-circle-question"></i> 检查更新失败
                            <p class="small mt-1">可能原因：API地址无法访问</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3 shadow-sm <?php echo $file_permissions_ok ? 'border-success' : 'border-danger' ?>">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="fa-solid fa-folder-open"></i> 文件权限</h6>
                    <h4 class="mt-3 mb-3">
                        <?php if ($file_permissions_ok): ?>
                            <span class="text-success"><i class="fa-solid fa-check-circle fa-2x"></i></span>
                            <p class="small">目录可写，可以正常更新</p>
                        <?php else: ?>
                            <span class="text-danger"><i class="fa-solid fa-times-circle fa-2x"></i></span>
                            <p class="small">部分目录不可写，可能影响更新</p>
                        <?php endif; ?>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3 shadow-sm <?php echo $disk_space_ok ? 'border-success' : 'border-danger' ?>">
                <div class="card-body text-center">
                    <h6 class="card-title"><i class="fa-solid fa-hard-drive"></i> 磁盘空间</h6>
                    <h4 class="mt-3 mb-3">
                        <?php if ($disk_space_ok): ?>
                            <span class="text-success"><i class="fa-solid fa-check-circle fa-2x"></i></span>
                            <p class="small">磁盘空间充足</p>
                        <?php else: ?>
                            <span class="text-danger"><i class="fa-solid fa-times-circle fa-2x"></i></span>
                            <p class="small">磁盘空间不足（需至少50MB）</p>
                        <?php endif; ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <!-- 自动更新弹窗 -->
    <?php if ($update_info && $update_info['has_update'] && $update_info['download_url']): ?>
    <div class="card mb-3 shadow-sm">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa-solid fa-cloud-download-alt"></i> 在线更新（推荐）</h6>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-1">
                        当前版本: <code>v<?php echo htmlspecialchars($update_info['current_version']) ?></code>
                        <i class="fa-solid fa-arrow-right mx-2"></i>
                        最新版本: <code class="text-success">v<?php echo htmlspecialchars($update_info['latest_version']) ?></code>
                    </p>
                    <?php if ($update_info['changelog']): ?>
                        <details class="mt-2">
                            <summary class="text-muted small">更新日志</summary>
                            <pre class="border p-2 mt-1 small" style="max-height: 200px; overflow-y: auto; background: #f8f9fa;"><?php echo htmlspecialchars($update_info['changelog']) ?></pre>
                        </details>
                    <?php endif; ?>
                    <?php if ($update_info['release_date']): ?>
                        <p class="small text-muted mb-0">发布日期: <?php echo htmlspecialchars($update_info['release_date']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                    <button class="btn btn-warning" onclick="showUpdateConfirm()">
                        <i class="fa-solid fa-cloud-download-alt"></i> 一键更新到 v<?php echo htmlspecialchars($update_info['latest_version']) ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 手动上传更新 -->
    <div class="card mb-3 shadow-sm">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa-solid fa-upload"></i> 手动上传更新包</h6>
        </div>
        <div class="card-body">
            <p class="text-muted small">如果在线更新不可用，您可以下载更新ZIP包后手动上传。</p>
            <form id="manualUpdateForm" enctype="multipart/form-data" onsubmit="return false;">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="updateZip" class="form-label">选择ZIP更新包</label>
                        <input class="form-control form-control-sm" type="file" id="updateZip" name="update_zip" accept=".zip">
                    </div>
                    <div class="col-md-3">
                        <label for="manualVersion" class="form-label">目标版本号</label>
                        <input type="text" class="form-control form-control-sm" id="manualVersion" name="version" placeholder="例如 1.1.0">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="manualUpdate()">
                            <i class="fa-solid fa-upload"></i> 上传更新
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 插件更新 -->
    <div class="card mb-3 shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fa-solid fa-puzzle-piece"></i> 插件更新</h6>
            <button class="btn btn-outline-primary btn-sm" onclick="checkPluginUpdates()">
                <i class="fa-solid fa-arrows-rotate"></i> 检查插件更新
            </button>
        </div>
        <div class="card-body">
            <div id="pluginUpdateArea">
                <?php if (!empty($plugin_updates)): ?>
                    <div class="alert alert-info py-2">
                        <i class="fa-solid fa-circle-info"></i>
                        发现 <?php echo count($plugin_updates) ?> 个插件有可用更新
                    </div>
                    <?php foreach ($plugin_updates as $pu): ?>
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?php echo htmlspecialchars($pu['title']) ?></strong>
                                    <span class="ms-2 small">
                                        <code><?php echo htmlspecialchars($pu['current_version']) ?></code>
                                        <i class="fa-solid fa-arrow-right mx-1"></i>
                                        <code class="text-success"><?php echo htmlspecialchars($pu['latest_version']) ?></code>
                                    </span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="doPluginUpdate('<?php echo htmlspecialchars($pu['name']) ?>', '<?php echo htmlspecialchars($pu['download_url']) ?>', '<?php echo htmlspecialchars($pu['latest_version']) ?>')">
                                    <i class="fa-solid fa-download"></i> 更新
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0">点击上方按钮检查插件更新</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 更新历史 -->
    <div class="card mb-3 shadow-sm">
        <div class="card-header">
            <h6 class="mb-0"><i class="fa-solid fa-clock-rotate-left"></i> 更新历史</h6>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($update_history)): ?>
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>目标</th>
                            <th>旧版本</th>
                            <th>新版本</th>
                            <th>状态</th>
                            <th>时间</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($update_history as $history): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($history['target']) ?></td>
                            <td><code><?php echo htmlspecialchars($history['from_version']) ?></code></td>
                            <td><code class="text-success"><?php echo htmlspecialchars($history['to_version']) ?></code></td>
                            <td>
                                <span class="badge bg-<?php echo $history['status'] === 'success' ? 'success' : 'danger' ?>">
                                    <?php echo $history['status'] === 'success' ? '成功' : '失败' ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?php echo date('Y-m-d H:i', $history['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-3 text-muted small">暂无更新记录</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 系统环境信息 -->
    <div class="card mb-3 shadow-sm">
        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#sysInfo" role="button" aria-expanded="false">
            <h6 class="mb-0"><i class="fa-solid fa-circle-info"></i> 系统环境信息 <i class="fa-solid fa-chevron-down float-end"></i></h6>
        </div>
        <div id="sysInfo" class="collapse">
            <div class="card-body">
                <table class="table table-sm table-bordered small">
                    <tbody>
                        <?php foreach ($system_info as $key => $value): ?>
                        <tr>
                            <td class="fw-bold" style="width: 180px;"><?php echo htmlspecialchars($key) ?></td>
                            <td><?php echo is_array($value) ? htmlspecialchars(implode(', ', array_keys(array_filter($value)))) : htmlspecialchars($value) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- 更新确认模态框 -->
<div class="modal fade" id="updateConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-triangle-exclamation text-warning"></i> 确认系统更新</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <p class="mb-1"><strong>请在更新前注意以下事项：</strong></p>
                    <ul class="mb-0 small">
                        <li>系统将自动备份当前版本文件</li>
                        <li>更新期间网站可能短暂无法访问</li>
                        <li>建议在非高峰时段进行更新</li>
                        <li>建议提前备份数据库</li>
                    </ul>
                </div>
                <p class="mb-0">
                    将更新至版本: <strong id="confirmVersion" class="text-success"><?php echo htmlspecialchars($update_info['latest_version'] ?? '') ?></strong>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-warning" id="confirmUpdateBtn" onclick="doSystemUpdate()">
                    <i class="fa-solid fa-check"></i> 确认更新
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showUpdateConfirm() {
        const modal = new bootstrap.Modal('#updateConfirmModal');
        modal.show();
    }

    function checkUpdate() {
        const load = Zap.loading('检查中...');
        $.ajax({
            url: '<?php echo Url::action('Update@ajaxCheckCore') ?>',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.code === 0 && data.data && data.data.has_update) {
                    ZapToast.alert('发现新版本 v' + data.data.latest_version, {bgColor: bgWarning, position: Toast_Pos_Center});
                } else {
                    ZapToast.alert('当前已是最新版本', {bgColor: bgSuccess, position: Toast_Pos_Center});
                }
                setTimeout(() => location.reload(), 1500);
            },
            error: function () {
                ZapToast.alert('检查更新失败', {bgColor: bgDanger, position: Toast_Pos_Center});
            }
        }).always(function () {
            load.dispose();
        });
    }

    function doSystemUpdate() {
        <?php if (!($update_info['download_url'] ?? false)): ?>
            ZapToast.alert('更新地址不可用', {bgColor: bgDanger, position: Toast_Pos_Center});
            return;
        <?php endif; ?>

        const btn = $('#confirmUpdateBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> 更新中...');

        const modal = bootstrap.Modal.getInstance('#updateConfirmModal');

        $.ajax({
            url: '<?php echo Url::action('Update@doUpdate') ?>',
            method: 'POST',
            dataType: 'json',
            data: {
                download_url: '<?php echo addslashes($update_info['download_url'] ?? '') ?>',
                version: '<?php echo addslashes($update_info['latest_version'] ?? '') ?>'
            },
            success: function (data) {
                if (data.code === 0) {
                    modal.hide();
                    ZapToast.alert('更新成功！系统将刷新页面', {bgColor: bgSuccess, position: Toast_Pos_Center});
                    setTimeout(() => location.reload(), 3000);
                } else {
                    ZapToast.alert(data.msg || '更新失败', {bgColor: bgDanger, position: Toast_Pos_Center});
                    btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> 确认更新');
                }
            },
            error: function () {
                ZapToast.alert('请求失败', {bgColor: bgDanger, position: Toast_Pos_Center});
                btn.prop('disabled', false).html('<i class="fa-solid fa-check"></i> 确认更新');
            }
        });
    }

    function manualUpdate() {
        const fileInput = document.getElementById('updateZip');
        const versionInput = document.getElementById('manualVersion');

        if (!fileInput.files.length) {
            ZapToast.alert('请选择ZIP更新包', {bgColor: bgWarning, position: Toast_Pos_Center});
            return;
        }
        if (!versionInput.value.trim()) {
            ZapToast.alert('请输入目标版本号', {bgColor: bgWarning, position: Toast_Pos_Center});
            return;
        }

        const load = Zap.loading('正在上传更新...');
        const formData = new FormData(document.getElementById('manualUpdateForm'));
        formData.append('version', versionInput.value.trim());

        $.ajax({
            url: '<?php echo Url::action('Update@manualUpdate') ?>',
            method: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert('更新成功！页面即将刷新', {bgColor: bgSuccess, position: Toast_Pos_Center});
                    setTimeout(() => location.reload(), 3000);
                } else {
                    ZapToast.alert(data.msg || '更新失败', {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            },
            error: function () {
                ZapToast.alert('上传失败', {bgColor: bgDanger, position: Toast_Pos_Center});
            }
        }).always(function () {
            load.dispose();
        });
    }

    function checkPluginUpdates() {
        const load = Zap.loading('检查中...');
        $.ajax({
            url: '<?php echo Url::action('Update@ajaxCheckPlugins') ?>',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                let html = '';
                if (data.code === 0 && data.data.length > 0) {
                    html += `<div class="alert alert-info py-2"><i class="fa-solid fa-circle-info"></i> 发现 ${data.count} 个插件有可用更新</div>`;
                    data.data.forEach(function (item) {
                        html += `
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${escapeHtml(item.title)}</strong>
                                    <span class="ms-2 small">
                                        <code>${escapeHtml(item.current_version)}</code>
                                        <i class="fa-solid fa-arrow-right mx-1"></i>
                                        <code class="text-success">${escapeHtml(item.latest_version)}</code>
                                    </span>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="doPluginUpdate('${escapeHtml(item.name)}', '${escapeHtml(item.download_url)}', '${escapeHtml(item.latest_version)}')">
                                    <i class="fa-solid fa-download"></i> 更新
                                </button>
                            </div>
                        </div>`;
                    });
                } else {
                    html = '<p class="text-success small mb-0"><i class="fa-solid fa-check"></i> 所有插件都是最新版本</p>';
                }
                $('#pluginUpdateArea').html(html);
            },
            error: function () {
                $('#pluginUpdateArea').html('<p class="text-danger small mb-0">检查失败，请稍后重试</p>');
            }
        }).always(function () {
            load.dispose();
        });
    }

    function doPluginUpdate(name, downloadUrl, version) {
        if (!confirm('确定要更新此插件到 v' + version + ' 吗？')) {
            return;
        }
        const load = Zap.loading('正在更新...');
        $.ajax({
            url: '<?php echo Url::action('Plugin@update') ?>',
            method: 'POST',
            dataType: 'json',
            data: {name: name, download_url: downloadUrl, version: version},
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert('更新成功！', {bgColor: bgSuccess, position: Toast_Pos_Center});
                    setTimeout(() => location.reload(), 1500);
                } else {
                    ZapToast.alert(data.msg || '更新失败', {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            }
        }).always(function () {
            load.dispose();
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
