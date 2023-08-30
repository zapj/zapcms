<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZAP</title>
    <link href="<?php echo base_url();?>/assets/bootstrap/5.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/admin/css/default.css" rel="stylesheet">
    <script src="<?php echo base_url();?>/assets/jquery/jquery-3.7.1.min.js"></script>
    <style>
        html,
        body {
            height: 100%;
        }

        .form-signin {
            max-width: 330px;
            padding: 1rem;
        }

        .form-signin .form-floating:focus-within {
            z-index: 2;
        }

        .form-signin input[type="email"] {
            margin-bottom: -1px;
            border-bottom-right-radius: 0;
            border-bottom-left-radius: 0;
        }

        .form-signin input[type="password"] {
            margin-bottom: 10px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    </style>
</head>

<body class="d-flex align-items-center py-4 bg-body-tertiary">


<main class="form-signin w-100 m-auto">
    <form action="<?php echo \zap\facades\Url::action('Auth@signIn'); ?>" method="post" id="reqForm">
        <p class="text-center">
            <img class="mb-4 m-auto " src="<?php echo base_url();?>/assets/admin/img/zap_logo_green_rgb.svg" alt="ZAP" width="150" >
        </p>
<!--        <h1 class="text-center">ZAP</h1>-->

        <div class="form-floating">
            <input type="text" class="form-control rounded-bottom-0" id="username" name="username" placeholder="用户名">
            <label for="username">用户名</label>
        </div>
        <div class="form-floating">
            <input type="password" class="form-control" id="password" name="password" placeholder="密码">
            <label for="password">密码</label>
        </div>


        <button class="btn btn-success w-100 py-2" type="button" onclick="return loginSystem()">登录</button>
        <p class="mt-5 mb-3 text-body-secondary text-center">&copy; ZAP.CN <?php echo date('Y');?></p>

    </form>
</main>
<div class="toast-container p-3 top-0 start-50 translate-middle-x" id="topCenterToast" data-original-class="toast-container p-3"></div>

<script>
    function loginSystem(){
        $.ajax({
            url:'<?php echo url_action("Auth@signIn"); ?>',
            method:'post',
            dataType:'json',
            data:$('#reqForm').serialize(),
            success:function(data){

                if(data.code === 0){
                    ZapToast.alert(data.msg,{
                        bgColor:bgSuccess,
                        delay:2000,
                        callback:function(){
                            location.href=data.redirect_to;
                        }
                    });

                }else{
                    ZapToast.alert(data.msg,{bgColor:bgDanger})
                }
            }
        });

        return false;
    }

</script>
<script src="<?php echo base_url();?>/assets/bootstrap/5.3.1/js/bootstrap.bundle.min.js" ></script>
<script src="<?php echo base_url();?>/assets/admin/js/admin.js"></script></body>
</html>