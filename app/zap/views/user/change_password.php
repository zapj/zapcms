<?php
use zap\facades\Url;

$this->layout('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 zap-top-bar">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item "><a href="<?php echo Url::action('System@settings') ?>">设置</a></li>
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('User@changePassword') ?>">修改密码</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
            <button type="button" class="btn btn-success btn-sm" onclick="changePassword()"><i class="fa fa-save"></i> 保存</button>
        </div>
    </div>

</nav>
<main class="container zap-main">


    <div class="card shadow">
        <div class="card-header">修改密码</div>
        <div class="card-body">
            <div class="row mb-3">
                <label for="cur_password" class="col-sm-2 col-form-label">当前密码</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="cur_password" name="cur_password" placeholder="请输入当前密码" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="new_password" class="col-sm-2 col-form-label">请输入新密码</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="请输入新密码">
                </div>
            </div>
            <div class="row mb-3">
                <label for="renew_password" class="col-sm-2 col-form-label">再次输入新密码</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="renew_password" name="renew_password" placeholder="再次输入新密码">
                </div>
            </div>



        </div>
        <div class="card-footer text-center">
            <button type="button" class="btn btn-success" onclick="changePassword()">修改密码</button>
        </div>
    </div>


</main>
<script>
    $(function(){
        $.validate({ignore:''});
    })
    function changePassword(){
        if(!$.valid()){

        }
    }
</script>