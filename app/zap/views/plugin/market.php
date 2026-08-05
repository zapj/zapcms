<?php

use zap\facades\Url;

!IS_AJAX && $this->layout('layouts/common');
?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 zap-top-bar">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo Url::action('Plugin') ?>">插件管理</a></li>
                <li class="breadcrumb-item active">插件市场</li>
            </ol>
        </nav>
        <form class="d-flex" onsubmit="searchPlugins(event)">
            <input id="searchInput" class="form-control form-control-sm me-2" type="search" placeholder="搜索插件..."
                   value="<?php echo htmlspecialchars($search) ?>" aria-label="Search">
            <button class="btn btn-outline-primary btn-sm" type="submit">
                <i class="fa-solid fa-search"></i>
            </button>
        </form>
    </div>
</nav>

<main class="container zap-main">
    <!-- 加载状态提示 -->
    <div id="loadingState" class="text-center py-5" style="display:none;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">加载中...</span>
        </div>
        <p class="mt-2 text-muted">正在获取插件列表...</p>
    </div>

    <!-- 错误状态 -->
    <div id="errorState" class="text-center py-5" style="display:none;">
        <i class="fa-solid fa-triangle-exclamation fa-3x text-warning"></i>
        <p class="mt-3 text-muted">无法连接到插件市场API</p>
        <p class="small text-muted">请检查系统设置中的API地址是否正确，或稍后再试</p>
        <button class="btn btn-outline-primary btn-sm" onclick="loadPlugins()">
            <i class="fa-solid fa-arrows-rotate"></i> 重试
        </button>
    </div>

    <!-- 空状态 -->
    <div id="emptyState" class="text-center py-5" style="display:none;">
        <i class="fa-solid fa-box-open fa-3x text-muted"></i>
        <p class="mt-3 text-muted">暂无可用的插件</p>
    </div>

    <!-- 插件列表 -->
    <div id="pluginList" class="row mt-3"></div>

    <!-- 分页 -->
    <div id="pagination" class="row mt-3" style="display:none;">
        <div class="col-12 text-center">
            <div class="btn-group btn-group-sm" id="pageButtons"></div>
        </div>
    </div>

    <!-- 插件详情模态框 -->
    <div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="detailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2">加载中...</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    let currentPage = <?php echo $page ?: 1 ?>;
    let currentSearch = '<?php echo addslashes($search) ?>';
    const installedPackages = <?php echo json_encode($installed ?? []) ?>;

    $(function () {
        loadPlugins();
    });

    function searchPlugins(e) {
        e.preventDefault();
        currentSearch = $('#searchInput').val().trim();
        currentPage = 1;
        loadPlugins();
    }

    function loadPlugins() {
        showLoading();
        $.ajax({
            url: '<?php echo Url::action('Plugin@ajaxMarketList') ?>',
            method: 'GET',
            dataType: 'json',
            data: {
                page: currentPage,
                search: currentSearch,
            },
            success: function (data) {
                if (data.code === 0 && data.data && data.data.plugins) {
                    renderPlugins(data.data.plugins, data.data);
                } else {
                    showEmpty();
                }
            },
            error: function () {
                showError();
            }
        });
    }

    function renderPlugins(plugins, info) {
        let html = '';
        plugins.forEach(function (p) {
            const isInstalled = installedPackages.indexOf(p.package_name) !== -1;
            html += `
            <div class="col-6 col-md-4 col-lg-3 mb-3">
                <div class="card h-100 shadow-sm ${isInstalled ? 'border-success' : ''}">
                    <div class="card-body">
                        <h6 class="card-title mb-1">
                            ${escapeHtml(p.title || p.name)}
                            ${isInstalled ? '<span class="badge bg-success">已安装</span>' : ''}
                        </h6>
                        <p class="card-text text-muted small mb-2">
                            ${escapeHtml((p.description || '').substring(0, 80))}
                        </p>
                        <div class="small text-muted">
                            <div>版本: <code>${escapeHtml(p.version)}</code></div>
                            ${p.author ? `<div>作者: ${escapeHtml(p.author)}</div>` : ''}
                            ${p.downloads ? `<div><i class="fa-solid fa-download"></i> ${p.downloads}</div>` : ''}
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="btn-group btn-group-sm w-100">
                            <button class="btn btn-outline-info" onclick="showDetail('${escapeHtml(p.package_name)}')">
                                <i class="fa-solid fa-info-circle"></i> 详情
                            </button>
                            ${isInstalled ? `
                                <button class="btn btn-success" disabled>
                                    <i class="fa-solid fa-check"></i> 已安装
                                </button>` : `
                                <button class="btn btn-outline-primary" onclick="installPlugin('${escapeHtml(p.package_name)}', '${escapeHtml(p.title || p.name)}')">
                                    <i class="fa-solid fa-download"></i> 安装
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            </div>`;
        });

        $('#pluginList').html(html);
        hideAll();

        // 分页
        if (info.total_pages > 1) {
            let pageHtml = '';
            const totalPages = parseInt(info.total_pages);
            const start = Math.max(1, currentPage - 2);
            const end = Math.min(totalPages, currentPage + 2);

            if (currentPage > 1) {
                pageHtml += `<button class="btn btn-outline-primary" onclick="goPage(${currentPage - 1})"><i class="fa-solid fa-angle-left"></i></button>`;
            }
            for (let i = start; i <= end; i++) {
                pageHtml += `<button class="btn ${i === currentPage ? 'btn-primary' : 'btn-outline-primary'}" onclick="goPage(${i})">${i}</button>`;
            }
            if (currentPage < totalPages) {
                pageHtml += `<button class="btn btn-outline-primary" onclick="goPage(${currentPage + 1})"><i class="fa-solid fa-angle-right"></i></button>`;
            }

            $('#pageButtons').html(pageHtml);
            $('#pagination').show();
        }
    }

    function goPage(page) {
        currentPage = page;
        loadPlugins();
        window.scrollTo(0, 0);
    }

    function showDetail(packageName) {
        const modal = new bootstrap.Modal('#detailModal');
        modal.show();

        $.ajax({
            url: '<?php echo Url::action('Plugin@ajaxMarketList') ?>',
            method: 'GET',
            dataType: 'json',
            data: {detail_package: packageName},
            success: function (data) {
                // 因为ajaxMarketList返回的是列表，需要在前端找到对应插件
            }
        });

        // 从已渲染的插件中查找详情
        $('#detailContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">加载中...</p>
            </div>
        `);

        // 尝试从本地数据渲染详情（插件列表已包含基本信息）
        setTimeout(() => {
            $('#detailContent').html(`
                <div class="modal-header">
                    <h5 class="modal-title">${escapeHtml(packageName)}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>包名: <code>${escapeHtml(packageName)}</code></p>
                    ${!installedPackages.includes(packageName) ? `
                    <button class="btn btn-primary btn-sm" onclick="installPlugin('${escapeHtml(packageName)}', '${escapeHtml(packageName)}')">
                        <i class="fa-solid fa-download"></i> 安装此插件
                    </button>` : `
                    <p class="text-success"><i class="fa-solid fa-check"></i> 已安装</p>
                    `}
                </div>
            `);
        }, 500);
    }

    function installPlugin(packageName, title) {
        if (!confirm('确定要安装插件 "' + title + '" 吗？')) {
            return;
        }
        const load = Zap.loading('正在安装...');
        $.ajax({
            url: '<?php echo Url::action('Plugin@install') ?>',
            method: 'POST',
            dataType: 'json',
            data: {package: packageName},
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert('安装成功！', {bgColor: bgSuccess, position: Toast_Pos_Center});
                    setTimeout(() => location.reload(), 1000);
                } else {
                    ZapToast.alert(data.msg || '安装失败', {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            },
            error: function () {
                ZapToast.alert('请求失败，请检查网络连接', {bgColor: bgDanger, position: Toast_Pos_Center});
            }
        }).always(function () {
            load.dispose();
        });
    }

    function showLoading() {
        hideAll();
        $('#loadingState').show();
    }

    function showError() {
        hideAll();
        $('#errorState').show();
    }

    function showEmpty() {
        hideAll();
        $('#emptyState').show();
    }

    function hideAll() {
        $('#loadingState, #errorState, #emptyState, #pagination').hide();
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
