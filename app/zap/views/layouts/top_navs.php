<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-success" aria-label="Main navigation">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo \zap\facades\Url::home();?>">
            <img class="" src="<?php echo base_url();?>/assets/admin/img/zap_logo_green_rgb.svg" alt="ZAP" width="120" >
        </a>
        <button class="navbar-toggler p-0 border-0" type="button" id="navbarSideCollapse" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-collapse offcanvas-collapse" id="navbarsTopMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?php echo \zap\facades\Url::home();?>"><i class="fa fa-dashboard"></i> 控制面板</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa fa-cube"></i> 内容管理</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fa fa-ellipsis"></i> 栏目</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-gear"></i> 设置</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo \zap\facades\Url::action('System@settings');?>">系统设置</a></li>
                        <li><a class="dropdown-item" href="#">Another action</a></li>
                        <li><a class="dropdown-item" href="#">Something else here</a></li>
                    </ul>
                </li>
            </ul>


            <ul class="navbar-nav d-flex mb-2 mb-lg-0">

                <!--                <li class="nav-item">-->
                <!--                    <a class="nav-link" href="#">Switch account</a>-->
                <!--                </li>-->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user-cog"></i> <?php echo $zap_admin['username']; ?></a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo \zap\facades\Url::action('User@changePassword'); ?>">
                                <i class="fa fa-key"></i> 修改密码</a></li>
                        <li><a class="dropdown-item" href="<?php echo \zap\facades\Url::action('Auth@signOut'); ?>">
                                <i class="fa fa-sign-out"></i> 安全退出</a></li>

                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
