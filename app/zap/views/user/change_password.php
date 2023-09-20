<?php
use zap\facades\Url;
\zap\Asset::library('jqueryvalidation');
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
<form id="zForm">
<main class="container zap-main">


    <div class="card shadow">
        <div class="card-header">修改密码</div>
        <div class="card-body">
            <div class="row mb-3">
                <label for="cur_password" class="col-sm-2 col-form-label">当前密码</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="cur_password" name="cur_password" placeholder="请输入当前密码" autocomplete="off" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="new_password" class="col-sm-2 col-form-label">请输入新密码</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="请输入新密码" required>
                </div>
            </div>
            <div class="row mb-3">
                <label for="renew_password" class="col-sm-2 col-form-label">再次输入新密码</label>
                <div class="col-sm-10">
                    <input type="password" class="form-control" id="renew_password" name="renew_password" placeholder="再次输入新密码" required>
                </div>
            </div>



        </div>
        <div class="card-footer text-center">
            <button type="button" class="btn btn-success" onclick="changePassword()">修改密码</button>
        </div>
    </div>


</main>
</form>
<script>
    $(function(){
        $('#zForm').validate({
            rules:{
                new_password:{
                    required: true,
                    rangelength: [6, 18],
                    pattern:/^[a-zA-Z0-9.@#$]{6,18}$/
                },
                renew_password: {
                    required: true,
                    equalTo:"#new_password",
                    rangelength: [6, 18],
                    pattern:/^[a-zA-Z0-9.@#$]{6,18}$/
                }
            },
            messages:{
                cur_password:"当前密码必须填写",
                new_password:{
                    required:"请输入新密码",
                    rangelength:"密码长度必须为6~18位的字符或数字",
                    pattern:"密码必须由6~18位字符组合,支持以下字符 [a-zA-Z0-9.@#$] "
                },
                renew_password:{
                    required:"请再次输入新密码",
                    rangelength:"密码长度必须为6~18位的字符或数字",
                    pattern:"密码必须由6~18位字符组合,支持以下字符 [a-zA-Z0-9.@#$] "
                },
            }
        });
    })
    function changePassword(){
        const zForm = $('#zForm');
        if(!zForm.valid()){
            return false;
        }
        $.ajax({
            url: '<?php echo Url::current();?>',
            method: 'post',
            data: zForm.serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center,callback:function(){
                        location.href='<?php echo url_action('Auth@signIn');?>';
                        }});
                    $('#zForm')[0].reset();
                } else {
                    ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            }
        });

    }
</script>