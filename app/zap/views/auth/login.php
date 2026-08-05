<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZAP CMS - 登录</title>
    <link href="<?php echo base_url();?>/assets/admin/css/bootstrap.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/admin/css/adminlte.min.css" rel="stylesheet">
    <script src="<?php echo base_url();?>/assets/jquery/jquery-3.6.4.min.js"></script>
    <link href="<?php echo base_url();?>/assets/font-awesome-6.4.2/css/all.css" rel="stylesheet">
    <style>
        :root {
            --zap-green: #10b981;
            --zap-green-dark: #059669;
        }
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 50%, #f0fdf4 100%);
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem 2rem;
            border-radius: 1rem;
            border: none;
        }
        .login-logo {
            width: 160px;
            margin-bottom: 1.5rem;
        }
        .login-card .form-control:focus {
            border-color: var(--zap-green);
            box-shadow: 0 0 0 0.2rem rgba(16, 185, 129, 0.15);
        }
        .btn-login {
            --bs-btn-bg: var(--zap-green);
            --bs-btn-border-color: var(--zap-green);
            --bs-btn-hover-bg: var(--zap-green-dark);
            --bs-btn-hover-border-color: var(--zap-green-dark);
            --bs-btn-active-bg: var(--zap-green-dark);
            --bs-btn-active-border-color: var(--zap-green-dark);
            font-weight: 600;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body class="login-page">
    <div class="card shadow-lg login-card">
        <div class="text-center mb-3">
            <img class="login-logo" src="<?php echo base_url();?>/assets/admin/img/zap_logo_green.svg" alt="ZAP CMS" onerror="this.outerHTML='<h2 class=\'fw-bold text-success\'>ZAP CMS</h2>'">
        </div>

        <form action="<?php echo \zap\facades\Url::action('Auth@signIn'); ?>" method="post" id="reqForm" enctype="multipart/form-data">
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="用户名" autocomplete="username" autofocus>
                <label for="username"><i class="fa fa-user me-1"></i>用户名</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="password" name="password" placeholder="密码" autocomplete="current-password">
                <label for="password"><i class="fa fa-lock me-1"></i>密码</label>
            </div>

            <button class="btn btn-login btn-success w-100 py-2" type="button" onclick="return loginSystem()">
                <i class="fa fa-sign-in-alt me-1"></i>登录
            </button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">&copy; <a href="https://zap.cn" class="text-decoration-none text-muted">ZAP.CN</a> <?php echo date('Y');?></p>
    </div>

    <!-- Toast 消息容器 -->
    <div class="toast-container p-3 top-0 start-50 translate-middle-x" id="topCenterToast" data-original-class="toast-container p-3"></div>
    <div class="toast-container p-3 top-0 end-0" id="topRightToast" data-original-class="toast-container p-3"></div>
    <div class="toast-container p-3 top-50 start-50 translate-middle" id="centerToast" data-original-class="toast-container p-3"></div>
    <div class="toast-container p-3 bottom-0 end-0" id="bottomRightToast" data-original-class="toast-container p-3"></div>

    <script>
        function loginSystem(){
            $.ajax({
                url:'<?php echo url_action("Auth@signIn"); ?>',
                type:'POST',
                dataType:'json',
                data: $('#reqForm').serialize(),
                success:function(data){
                    if(data.code === 0){
                        ZapToast.alert(data.msg,{
                            bgColor:bgSuccess,
                            delay:1000,
                            callback:function(){
                                location.href=data.redirect_to;
                            }
                        });
                    }else{
                        ZapToast.alert(data.msg,{bgColor:bgDanger})
                    }
                },error:function(data){
                    console.log(data)
                }
            });
            return false;
        }

        // 回车键登录
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                loginSystem();
            }
        });
    </script>
    <script src="<?php echo base_url();?>/assets/admin/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/admin.js"></script>
    <script>
        <?php
        \zap\cms\AdminPage::instance()->showFlashMessages();
        ?>
    </script>
</body>
</html>
