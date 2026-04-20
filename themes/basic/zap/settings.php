<?php
defined('IN_ZAP_ADMIN') or die('No permission');

$page = req()->get('page','_settings');
?>
    <nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 ">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                 aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active"><a href="<?php echo url_action('theme') ?>">主题管理</a></li>
                </ol>
            </nav>
            <div class=" text-end">
                <!--            <a href="#" class="btn btn-success btn-sm" ><i class="fa-solid fa-search"></i> 主题市场</a>-->

            </div>
        </div>
    </nav>


        <main class="container zap-main">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link <?php echo $page=='_settings' ? 'active':''; ?>" aria-current="page" href="<?php echo url_action('theme@settings')?>">首页设置</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $page=='test' ? 'active':''; ?>" href="<?php echo url_action('theme@settings?page=test')?>">文章</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $page=='image' ? 'active':''; ?>" href="<?php echo url_action('theme@settings?page=image')?>">图片设置</a>
                </li>

            </ul>
            <form action="" method="post" id="zapForm">
                <?php
                $view = theme_path('zap/'.$page.'.php');
                if(is_file($view)){
                    include $view;
                }
                ?>


            </form>
        </main>



