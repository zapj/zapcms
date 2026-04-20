<?php
$this->layout('layout');
?>



<div class="row g-5 justify-content-center">

    <div class="col-md-7 col-lg-8 ">
        <div class="card">
            <h5 class="card-header">配置完成</h5>
            <div class="card-body ">
                <form class="needs-validation" novalidate>
                    <div class="alert alert-success" role="alert">
                        ZapCMS系统安装完成
                        <hr>
                        <p>登录地址:<?php echo $url; ?></p>
                        <p>用户名: <?php echo $username; ?> </p>
                        <p>密码: <?php echo $password; ?></p>
                    </div>



                </form>
            </div>
            <div class="card-footer text-center">
                <a href="../<?php echo Z_ADMIN_PREFIX ?>" target="_blank" class="btn btn-success">控制面板</a>
                <a href="../" target="_blank" class="btn btn-success">网站首页</a>
            </div>
        </div>
    </div>
</div>
