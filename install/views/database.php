<?php
$this->extend('layout');
?>


    <div class="row g-5 justify-content-center">

        <div class="col-md-7 col-lg-8 ">
            <div class="card">
                <h5 class="card-header">配置数据库</h5>
                <div class="card-body ">
                    <form class="needs-validation" id="dbconfig" novalidate>
                        <div class="row g-3">

                            <h6>网站设置</h6>
                            <hr class="m-0"/>

                            <div class="col-6">
                                <label for="website-title" class="form-label">网站名称</label>
                                <input type="text" class="form-control form-control-sm" id="website-title" name="website[title]" placeholder="ZAP CMS" value="ZAP CMS">
                            </div>
                            <div class="col-6">
                                <label for="website-slogan" class="form-label">副标题</label>
                                <input type="text" class="form-control form-control-sm" id="website-slogan" name="website[slogan]" placeholder="OpenSource CMS" value="OpenSource CMS">
                            </div>

                            <div class="col-12">
                                <label for="website-email" class="form-label">邮箱 <span class="text-body-secondary">(可选)</span></label>
                                <input type="email" class="form-control form-control-sm" id="website-email" name="website[email]" placeholder="you@example.com" value="admin@<?php echo $_SERVER['HTTP_HOST']; ?>">
                            </div>

                            <div class="col-6">
                                <label for="website-username" class="form-label">用户名</label>
                                <input type="text" class="form-control form-control-sm" id="website-username" name="website[username]" placeholder="网站用户名" value="admin">
                            </div>
                            <div class="col-6">
                                <label for="website-password" class="form-label">密码</label>
                                <input type="text" class="form-control form-control-sm" id="website-password" name="website[password]" placeholder="网站密码">
                            </div>
                            <h6>数据库配置</h6>
                            <hr class="m-0"/>
                            <div class="col-4">
                                <label for="db-type" class="form-label">数据库类型</label>
                                <select id="db-driver" name="db[driver]"  class="form-select form-select-sm" onchange="dbTypeChange(this);">
                                    <option value="mysql">MySQL / MariaDB</option>
                                    <option value="sqlite">Sqlite3</option>
<!--                                        <option value="pgsql" disabled>PostgreSQL</option>-->
                                </select>
                            </div>
                            <div class="col-md-4 sqlite3">
                                <label for="db-host" class="form-label">主机名</label>
                                <input type="text" class="form-control form-control-sm" id="db-host" name="db[host]" placeholder="主机名 默认localhost" value="localhost" >
                            </div>
                            <div class="col-md-4 sqlite3">
                                <label for="db-port" class="form-label">端口</label>
                                <input type="text" class="form-control form-control-sm" id="db-port" name="db[port]" placeholder="端口号 mysql默认 3306" value="3306" >
                            </div>
                            <div class="col-md-4">
                                <label for="db-dbname" class="form-label">数据库名称</label>
                                <input type="text" class="form-control form-control-sm" id="db-dbname" name="db[dbname]" placeholder="数据库名称" value="zapcms" >
                            </div>

                            <div class="col-4 sqlite3">
                                <label for="db-user" class="form-label">用户名</label>
                                <input type="text" class="form-control form-control-sm" id="db-user" name="db[user]" placeholder="数据库用户名 , mysql 默认 root" value="root">
                            </div>
                            <div class="col-4 sqlite3">
                                <label for="db-password" class="form-label">密码</label>
                                <input type="text" class="form-control form-control-sm" id="db-password" name="db[password]" placeholder="数据库密码" value="root">
                            </div>

                            <div class="col-4">
                                <label for="db-table-prefix" class="form-label">表前缀</label>
                                <input type="text" class="form-control form-control-sm" id="db-table-prefix" name="db[prefix]" placeholder="表前缀" value="zap_">
                            </div>




                            <div class="col-md-12 text-black-50 overflow-y-auto d-none" id="installConsole" style="height: 100px;font-size: 12px">

                            </div>
                        </div>

                    </form>
                </div>
                <div class="card-footer text-center">
                    <button href="index.php?action=done" type="button" class="btn btn-success" onclick="installZapCMS(this)">立刻安装</button>
                </div>
            </div>
        </div>
    </div>
<script>
    function dbTypeChange(select){
        var sqlite3List = document.querySelectorAll('.sqlite3');
        if(select.value === 'sqlite'){
            sqlite3List.forEach(function (v){
                v.classList.add('d-none')
            });
        }else{
            sqlite3List.forEach(function (v){
                v.classList.remove('d-none')
            });
        }
    }

    function installZapCMS(btn){
        btn.disabled = true;
        var installError = false;
        const myConsole = document.getElementById('installConsole');
        myConsole.classList.remove('d-none');
        myConsole.innerHTML = '';
        myConsole.prepend(createDiv("开始安装...",'green',true));
        myConsole.prepend(createDiv("测试数据库连接",'green',true));

        $.ajax({
            url: 'index.php?action=checkDatabaseConnection',
            data: $('#dbconfig').serialize(),
            method:'post',
            async:false,
            success: function (data) {
                if (data.code === 1) {
                    myConsole.prepend(createDiv(`${data.msg} , ${data.exception}`, 'red', true));
                    installError = true;
                } else {
                    myConsole.prepend(createDiv(data.msg, 'green', true));
                }
            },
            error:function(){
                btn.disabled = false;
            }
        });

        if(installError){
            myConsole.prepend(createDiv("安装失败!!!",'red',true));
            return false;
        }

        $.ajax({
            url: 'index.php?action=createDBSchemaBaseData',
            data: $('#dbconfig').serialize(),
            method:'post',
            success: function (data) {
                if (data.code === 1) {
                    myConsole.prepend(createDiv(`${data.msg} , ${data.exception}`, 'red', true));
                    installError = true;
                } else {
                    myConsole.prepend(createDiv(data.msg, 'green', true));
                }
            }
        }).always(function(){
            btn.disabled = false;
        });


        if(installError){
            myConsole.prepend(createDiv("安装失败!!!",'red',true));
            return false;
        }
        location.href='index.php?action=done';

    }

    function createDiv(text,color,bold){
        bold = bold || true;
        color = color || 'black';
        const li = document.createElement("div");
        if(color){
            li.style.color = color;
        }
        // if(bold){
        //     li.style.fontWeight = "bold";
        // }
        li.innerHTML = '>' + text;
        return li;
    }


</script>

