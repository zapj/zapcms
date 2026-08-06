<?php
defined('IN_ZAP_ADMIN') or die('No permission');

$page = req()->get('page','_settings');
?>
     

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



