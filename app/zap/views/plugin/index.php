<?php

use zap\facades\Url;

!IS_AJAX && $this->layout('layouts/common');
?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 zap-top-bar">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('Plugin') ?>">插件管理</a></li>
            </ol>
        </nav>
        <div class="text-end">
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="checkUpdates()">
                <i class="fa-solid fa-arrows-rotate"></i> 检查更新
            </button>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="showUploadModal()">
                <i class="fa-solid fa-upload"></i> 上传安装
            </button>
            <a href="<?php echo Url::action('Plugin@market') ?>" class="btn btn-info btn-sm">
                <i class="fa-solid fa-store"></i> 插件市场
            </a>
        </div>
    </div>
</nav>

<main class="container zap-main">
    <div class="row mt-3">
        <div class="col-12 mb-3 border-bottom">
            <h5 class="pb-2 mb-0"><i class="fa-solid fa-puzzle-piece"></i> 已安装插件</h5>
        </div>

        <?php if (empty($plugins) && empty($loaded_mods)): ?>
        <div class="col-12">
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-box-open" style="font-size: 48px;"></i>
                <p class="mt-3">暂无安装任何插件</p>
                <a href="<?php echo Url::action('Plugin@market') ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-store"></i> 前往插件市场
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php foreach ($plugins as $plugin): ?>
        <div class="col-6 col-md-4 col-lg-3 mb-3">
            <div class="card h-100 shadow-sm <?php echo $plugin['status'] ? '' : 'border-secondary opacity-75' ?>">
                <div class="card-body">
                    <h6 class="card-title mb-1">
                        <?php echo htmlspecialchars($plugin['title'] ?: $plugin['name']) ?>
                        <?php if (!$plugin['status']): ?>
                            <span class="badge bg-secondary">已禁用</span>
                        <?php else: ?>
                            <span class="badge bg-success">运行中</span>
                        <?php endif; ?>
                    </h6>
                    <p class="card-text text-muted small mb-1">
                        <?php $desc = $plugin['description'] ?? ''; echo htmlspecialchars(mb_strlen($desc) > 80 ? mb_substr($desc, 0, 80) . '...' : $desc) ?>
                    </p>
                    <div class="small text-muted">
                        <div>版本: <code><?php echo htmlspecialchars($plugin['version']) ?></code></div>
                        <?php if ($plugin['author']): ?>
                        <div>作者: <?php echo htmlspecialchars($plugin['author']) ?></div>
                        <?php endif; ?>
                        <?php if ($plugin['package_name']): ?>
                        <div>包名: <code><?php echo htmlspecialchars($plugin['package_name']) ?></code></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <?php if ($plugin['status']): ?>
                            <button class="btn btn-outline-warning" onclick="togglePlugin('<?php echo $plugin['name'] ?>', 0)" title="禁用">
                                <i class="fa-solid fa-pause"></i>
                            </button>
                        <?php else: ?>
                            <button class="btn btn-outline-success" onclick="togglePlugin('<?php echo $plugin['name'] ?>', 1)" title="启用">
                                <i class="fa-solid fa-play"></i>
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-outline-danger" onclick="uninstallPlugin('<?php echo $plugin['name'] ?>')" title="卸载">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <?php foreach ($loaded_mods as $mod): ?>
            <?php if (in_array($mod['name'], $registered_names)) continue; ?>
            <div class="col-6 col-md-4 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm border-warning">
                    <div class="card-body">
                        <h6 class="card-title mb-1">
                            <?php echo htmlspecialchars($mod['title']) ?>
                            <span class="badge bg-warning text-dark">未注册</span>
                        </h6>
                        <div class="small text-muted">
                            <div>名称: <code><?php echo htmlspecialchars($mod['name']) ?></code></div>
                            <?php if ($mod['version']): ?>
                            <div>版本: <?php echo htmlspecialchars($mod['version']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- 上传安装模态框 -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-upload"></i> 上传插件安装</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="pluginZip" class="form-label">选择插件ZIP包</label>
                        <input class="form-control" type="file" id="pluginZip" name="plugin_zip" accept=".zip">
                        <div class="form-text">仅支持 .zip 格式的插件包，插件包应包含 plugin.json</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" onclick="uploadInstall()">
                    <i class="fa-solid fa-check"></i> 安装
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 更新列表模态框 -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa-solid fa-arrows-rotate"></i> 插件更新检查 <span id="updateCount" class="badge bg-danger"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="updateModalBody">
                <div class="text-center py-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">检查中...</span>
                    </div>
                    <p class="mt-2">正在检查更新...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showUploadModal() {
        const modal = new bootstrap.Modal('#uploadModal');
        modal.show();
    }

    function uploadInstall() {
        const fileInput = document.getElementById('pluginZip');
        if (!fileInput.files.length) {
            ZapToast.alert('请选择ZIP文件', {bgColor: bgWarning, position: Toast_Pos_Center});
            return;
        }

        const load = Zap.loading('正在安装...');
        const formData = new FormData(document.getElementById('uploadForm'));

        $.ajax({
            url: '<?php echo Url::action('Plugin@uploadInstall') ?>',
            method: 'POST',
            data: formData,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center});
                    setTimeout(() => location.reload(), 1000);
                } else {
                    ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            },
            error: function () {
                ZapToast.alert('请求失败', {bgColor: bgDanger, position: Toast_Pos_Center});
            }
        }).always(function () {
            load.dispose();
        });
    }

    function uninstallPlugin(name) {
        if (!confirm('确定要卸载插件 "' + name + '" 吗？此操作不可恢复。')) {
            return;
        }
        const load = Zap.loading('正在卸载...');
        $.ajax({
            url: '<?php echo Url::action('Plugin@uninstall') ?>',
            method: 'POST',
            dataType: 'json',
            data: {name: name},
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center});
                    setTimeout(() => location.reload(), 1000);
                } else {
                    ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            },
            error: function () {
                ZapToast.alert('请求失败', {bgColor: bgDanger, position: Toast_Pos_Center});
            }
        }).always(function () {
            load.dispose();
        });
    }

    function togglePlugin(name, status) {
        const action = status ? '启用' : '禁用';
        if (!confirm('确定要' + action + '此插件吗？')) {
            return;
        }
        const load = Zap.loading('正在' + action + '...');
        $.ajax({
            url: '<?php echo Url::action('Plugin@toggleStatus') ?>',
            method: 'POST',
            dataType: 'json',
            data: {name: name, status: status},
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center});
                    setTimeout(() => location.reload(), 1000);
                } else {
                    ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            }
        }).always(function () {
            load.dispose();
        });
    }

    function checkUpdates() {
        const modal = new bootstrap.Modal('#updateModal');
        modal.show();

        $.ajax({
            url: '<?php echo Url::action('Plugin@checkUpdates') ?>',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                let html = '';
                if (data.code === 0 && data.data.length > 0) {
                    $('#updateCount').text(data.count).show();
                    data.data.forEach(function (item) {
                        html += `
                        <div class="card mb-2 border-warning">
                            <div class="card-body">
                                <h6 class="card-title">${escapeHtml(item.title)}</h6>
                                <p class="mb-1">
                                    <span class="badge bg-secondary">${escapeHtml(item.current_version)}</span>
                                    <i class="fa-solid fa-arrow-right mx-1"></i>
                                    <span class="badge bg-success">${escapeHtml(item.latest_version)}</span>
                                </p>
                                ${item.changelog ? `<pre class="small text-muted border p-2 mt-2" style="max-height:120px;overflow-y:auto;">${escapeHtml(item.changelog)}</pre>` : ''}
                            </div>
                            <div class="card-footer text-end">
                                <button class="btn btn-primary btn-sm" onclick="doPluginUpdate('${escapeHtml(item.name)}', '${escapeHtml(item.download_url)}', '${escapeHtml(item.latest_version)}')">
                                    <i class="fa-solid fa-download"></i> 更新
                                </button>
                            </div>
                        </div>`;
                    });
                } else {
                    html = '<div class="text-center py-3 text-success"><i class="fa-solid fa-circle-check fa-2x"></i><p class="mt-2">所有插件都是最新版本</p></div>';
                    $('#updateCount').hide();
                }
                $('#updateModalBody').html(html);
            },
            error: function () {
                $('#updateModalBody').html('<div class="text-center py-3 text-danger">检查更新失败，请确保API地址可访问</div>');
            }
        });
    }

    function doPluginUpdate(name, downloadUrl, version) {
        const load = Zap.loading('正在更新插件...');
        $.ajax({
            url: '<?php echo Url::action('Plugin@update') ?>',
            method: 'POST',
            dataType: 'json',
            data: {
                name: name,
                download_url: downloadUrl,
                version: version
            },
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center});
                    setTimeout(() => location.reload(), 1500);
                } else {
                    ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
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
