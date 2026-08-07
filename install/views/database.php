<?php $this->extend('layout'); ?>

<div class="install-card card">
    <div class="card-header">
        <span class="check-pass me-2">&#9881;</span> 数据库与站点配置
    </div>
    <div class="card-body">
        <form id="installForm" autocomplete="off">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <!-- 站点信息 -->
            <h6 class="fw-bold mb-3">站点信息</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="websiteTitle" class="form-label small fw-semibold">网站名称</label>
                    <input type="text" class="form-control form-control-sm" id="websiteTitle"
                           name="website[title]" placeholder="我的网站" value="ZAP CMS" required>
                </div>
                <div class="col-md-6">
                    <label for="websiteSlogan" class="form-label small fw-semibold">副标题</label>
                    <input type="text" class="form-control form-control-sm" id="websiteSlogan"
                           name="website[slogan]" placeholder="副标题" value="OpenSource CMS">
                </div>
                <div class="col-md-6">
                    <label for="websiteEmail" class="form-label small fw-semibold">邮箱 <span class="text-muted fw-normal">(可选)</span></label>
                    <input type="email" class="form-control form-control-sm" id="websiteEmail"
                           name="website[email]" placeholder="admin@example.com"
                           value="admin@<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'localhost') ?>">
                </div>
                <div class="col-md-3">
                    <label for="adminUser" class="form-label small fw-semibold">管理员用户名</label>
                    <input type="text" class="form-control form-control-sm" id="adminUser"
                           name="website[username]" placeholder="admin" value="admin" required minlength="3">
                </div>
                <div class="col-md-3">
                    <label for="adminPass" class="form-label small fw-semibold">管理员密码</label>
                    <div class="input-group input-group-sm">
                        <input type="password" class="form-control form-control-sm" id="adminPass"
                               name="website[password]" placeholder="输入密码" required minlength="6">
                        <button class="btn btn-outline-secondary" type="button" id="toggleAdminPass" tabindex="-1">&#128065;</button>
                    </div>
                </div>
            </div>

            <hr>

            <!-- 数据库 -->
            <h6 class="fw-bold mb-3">数据库配置</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="dbDriver" class="form-label small fw-semibold">数据库类型</label>
                    <select class="form-select form-select-sm" id="dbDriver" name="db[driver]">
                        <option value="mysql">MySQL / MariaDB</option>
                        <option value="sqlite">SQLite</option>
                    </select>
                </div>
                <div class="col-md-4 mysql-only">
                    <label for="dbHost" class="form-label small fw-semibold">主机</label>
                    <input type="text" class="form-control form-control-sm" id="dbHost"
                           name="db[host]" placeholder="localhost" value="localhost">
                </div>
                <div class="col-md-4 mysql-only">
                    <label for="dbPort" class="form-label small fw-semibold">端口</label>
                    <input type="text" class="form-control form-control-sm" id="dbPort"
                           name="db[port]" placeholder="3306" value="3306">
                </div>
                <div class="col-md-4">
                    <label for="dbName" class="form-label small fw-semibold">数据库名</label>
                    <input type="text" class="form-control form-control-sm" id="dbName"
                           name="db[dbname]" placeholder="zapcms" value="zapcms">
                </div>
                <div class="col-md-4 mysql-only">
                    <label for="dbUser" class="form-label small fw-semibold">用户名</label>
                    <input type="text" class="form-control form-control-sm" id="dbUser"
                           name="db[username]" placeholder="root" value="root">
                </div>
                <div class="col-md-4 mysql-only">
                    <label for="dbPass" class="form-label small fw-semibold">密码</label>
                    <div class="input-group input-group-sm">
                        <input type="password" class="form-control form-control-sm" id="dbPass"
                               name="db[password]" placeholder="数据库密码" value="root">
                        <button class="btn btn-outline-secondary" type="button" id="toggleDbPass" tabindex="-1">&#128065;</button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="dbPrefix" class="form-label small fw-semibold">表前缀</label>
                    <input type="text" class="form-control form-control-sm" id="dbPrefix"
                           name="db[prefix]" placeholder="zap_" value="zap_">
                </div>
            </div>

            <!-- 安装控制台 -->
            <div id="consoleWrap" class="d-none">
                <label class="form-label small fw-semibold">安装日志</label>
                <div class="install-console" id="installConsole"></div>
            </div>
        </form>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <a href="index.php?action=check" class="btn btn-outline-secondary btn-sm">&larr; 上一步</a>
        <button type="button" class="btn btn-success px-4" id="installBtn" onclick="installZapCMS()">
            开始安装
        </button>
    </div>
</div>

<script>
(function(){
    var driverSelect = document.getElementById('dbDriver');
    var mysqlFields  = document.querySelectorAll('.mysql-only');

    function toggleMysqlFields(){
        var isMysql = driverSelect.value !== 'sqlite';
        mysqlFields.forEach(function(el){ el.classList.toggle('d-none', !isMysql); });
    }
    driverSelect.addEventListener('change', toggleMysqlFields);
    // 初始状态（默认 MySQL）
    // toggleMysqlFields();  // 如果默认选中 mysql 就调用

    // 密码可见性切换
    document.getElementById('toggleAdminPass').addEventListener('click', function(){
        var inp = document.getElementById('adminPass');
        inp.type = inp.type === 'password' ? 'text' : 'password';
    });
    document.getElementById('toggleDbPass').addEventListener('click', function(){
        var inp = document.getElementById('dbPass');
        inp.type = inp.type === 'password' ? 'text' : 'password';
    });
})();
</script>

<script>
function installZapCMS(){
    var btn = document.getElementById('installBtn');
    var form = document.getElementById('installForm');
    var consoleEl = document.getElementById('installConsole');
    var wrapEl = document.getElementById('consoleWrap');

    // 前端基础校验
    var adminUser = form.querySelector('[name="website[username]"]').value.trim();
    var adminPass = form.querySelector('[name="website[password]"]').value.trim();
    if (!adminUser || !adminPass) {
        alert('请填写管理员用户名和密码');
        return;
    }
    if (adminPass.length < 6) {
        alert('管理员密码至少 6 位');
        return;
    }

    btn.disabled = true;
    btn.textContent = '安装中...';
    wrapEl.classList.remove('d-none');
    consoleEl.innerHTML = '';
    log('开始安装 ZAP CMS', 'info');

    // ── 第 1 步：检测数据库连接 ──
    log('检测数据库连接...', 'info');
    $.ajax({
        url: 'index.php?action=checkDatabaseConnection',
        data: $(form).serialize(),
        method: 'POST'
    }).then(function(data){
        if (data.code !== 0) {
            log('数据库连接失败: ' + (data.msg || ''), 'err');
            if (data.detail) log('  ↳ ' + data.detail, 'err');
            throw new Error('db_connect');
        }
        log('数据库连接成功', 'ok');

        // ── 第 2 步：建表写配置 ──
        log('创建数据表 & 导入初始数据...', 'info');
        return $.ajax({
            url: 'index.php?action=createDBSchemaBaseData',
            data: $(form).serialize(),
            method: 'POST'
        });
    }).then(function(data){
        if (data.code !== 0) {
            log('安装失败: ' + (data.msg || ''), 'err');
            if (data.detail) log('  ↳ ' + data.detail, 'err');
            throw new Error('install_fail');
        }
        log('数据表创建完成', 'ok');
        log('初始数据导入完成', 'ok');
        log('配置文件写入完成', 'ok');
        log('安装成功！即将跳转...', 'end');

        // 跳转到完成页
        setTimeout(function(){
            location.href = 'index.php?action=done';
        }, 800);

    }).catch(function(err){
        if (err && err.message === 'db_connect' || err.message === 'install_fail') {
            // 已在上面记录了错误
        } else if (err && err.statusText === 'error') {
            // jQuery 网络层错误
            log('网络请求失败', 'err');
        }
    }).always(function(){
        btn.disabled = false;
        btn.textContent = '开始安装';
    });
}

function log(msg, type){
    var consoleEl = document.getElementById('installConsole');
    var now = new Date();
    var time = ('0' + now.getHours()).slice(-2) + ':'
             + ('0' + now.getMinutes()).slice(-2) + ':'
             + ('0' + now.getSeconds()).slice(-2);
    var cls = '';
    switch(type){
        case 'ok':   cls = 'ok';   break;
        case 'err':  cls = 'err';  break;
        case 'info': cls = 'info'; break;
        case 'end':  cls = 'end';  break;
    }
    var div = document.createElement('div');
    div.className = 'console-line ' + cls;
    div.innerHTML = '<span class="time">[' + time + ']</span>' + escapeHtml(msg);
    consoleEl.appendChild(div);
    consoleEl.scrollTop = consoleEl.scrollHeight;
}

function escapeHtml(str){
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}
</script>
