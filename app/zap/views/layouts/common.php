<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $this->e($title ?? 'ZAP CMS');?></title>
    <link href="<?php echo base_url();?>/assets/admin/css/overlayscrollbars.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/admin/css/zap-admin.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/admin/css/zap-admin-custom.css" rel="stylesheet">
    <?php echo $this->block('styles'); ?>
    <?php echo $this->block('head_js'); ?>
    <script src="<?php echo base_url();?>/assets/jquery/jquery-3.6.4.min.js"></script>
    <link href="<?php echo base_url();?>/assets/fontawesome/6.4.2/css/all.css" rel="stylesheet">
    <script>
        window.ZAP_BASE_URL = '<?php echo base_url();?>';
        window.ZAP_ASSETS_URL = '<?php echo base_url();?>/assets/admin';
    </script>
</head>

<body class="layout-fixed sidebar-expand-lg">
    <div class="app-wrapper">
        <!-- 顶部导航栏 -->
        <nav class="app-header navbar navbar-expand bg-white shadow-sm">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="fa fa-bars"></i>
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a href="<?php echo base_url();?>" class="nav-link" target="_blank">
                            <i class="fa fa-external-link-alt me-1"></i> 访问网站
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button">
                            <i class="fa fa-user-circle me-1"></i>
                            <span><?php echo htmlspecialchars(\zap\cms\Auth::user()['nickname'] ?? \zap\cms\Auth::user()['username'] ?? '管理员'); ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow">
                            <a class="dropdown-item" href="<?php echo \zap\facades\Url::action('System@settings'); ?>">
                                <i class="fa fa-cog me-2"></i>系统设置
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="<?php echo \zap\facades\Url::action('Auth@signOut'); ?>">
                                <i class="fa fa-sign-out-alt me-2"></i>退出登录
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- 左侧边栏 -->
        <?php $this->include('layouts/top_navs','top_navs');?>
        <?php echo $this->block('top_navs');?>

        <!-- 主内容区 -->
        <main class="app-main">
            <div class="app-content">
                <div class="container-fluid px-4 py-3">
                    <!-- 面包屑导航 -->
                    <?php if (!empty($page_title)): ?>
                    <div class="mb-3">
                        <h4 class="mb-0 fw-semibold"><?php echo htmlspecialchars($page_title); ?></h4>
                        <?php if (!empty($page_subtitle)): ?>
                        <small class="text-muted"><?php echo htmlspecialchars($page_subtitle); ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php echo $this->block('content');?>
                </div>
            </div>
        </main>

        <!-- 页脚 -->
        <footer class="app-footer">
            <div class="float-end d-none d-sm-block">
                <b><?php echo ZAP_CMS_VERSION; ?></b>
            </div>
            <strong>&copy; <a href="https://zap.cn" class="text-decoration-none">ZapCMS</a> <?php echo date('Y');?></strong>
        </footer>
    </div>

    <!-- Toast 消息容器 -->
    <div class="toast-container p-3 top-0 start-50 translate-middle-x" id="topCenterToast" data-original-class="toast-container p-3"></div>
    <div class="toast-container p-3 top-0 end-0" id="topRightToast" data-original-class="toast-container p-3"></div>
    <div class="toast-container p-3 top-50 start-50 translate-middle" id="centerToast" data-original-class="toast-container p-3"></div>
    <div class="toast-container p-3 bottom-0 end-0" id="bottomRightToast" data-original-class="toast-container p-3"></div>

    <?php echo $this->block('scripts'); ?>
    <script src="<?php echo base_url();?>/assets/admin/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/overlayscrollbars.browser.es6.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/zap-admin.js"></script>
    <script src="<?php echo base_url();?>/assets/admin/js/admin.js"></script>

    <script>
        <?php
        \zap\cms\AdminPage::instance()->showFlashMessages();
        ?>
    </script>
    <?php echo $this->block('page_scripts'); ?>
</body>
</html>
