<?php
use zap\facades\Url;
use zap\AdminMenu;
?>
<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-success" aria-label="Main navigation">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo Url::home();?>">
            <img class="" src="<?php echo base_url();?>/assets/admin/img/zap_logo_green_rgb.svg" alt="ZAP" width="120" >
        </a>
        <button class="navbar-toggler p-0 border-0" type="button" id="navbarSideCollapse" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-collapse offcanvas-collapse" id="navbarsTopMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo Url::active('index/index');?>" aria-current="page" href="<?php echo Url::home();?>"><i class="fa fa-dashboard"></i> 控制面板</a>
                </li>
                <?php
                $adminMenus = AdminMenu::instance()->getTreeArray();

                $childLastId = [];
                while($menu = array_shift($adminMenus)){
                    ?>
                    <li class="nav-item <?php echo !empty($menu['children']) ? 'dropdown':''; ?>">
                        <a data-id="<?php echo $menu['id'];?>" href="<?php echo Url::action($menu['link_to']);?>"
                        <?php if(!empty($menu['children'])) {
                            echo 'class="nav-link dropdown-toggle ', Url::active($menu['active_rule']) ,'" data-bs-toggle="dropdown" aria-expanded="false"';
                        }else if(count($childLastId)){
                            echo 'class="dropdown-item"';
                        }else{
                            echo 'class="nav-link ', Url::active($menu['active_rule']) ,'"';
                        } ?>
                        >
                            <i class="<?php echo $menu['icon'];?>"></i>
                            <?php echo $menu['title'];?>
                        </a>

                <?php
                    if(!empty($menu['children'])){
                        echo '<ul class="dropdown-menu">';
                        $childLastId[] = end($menu['children'])['id'];
                        while($children = array_pop($menu['children'])){
                            array_unshift($adminMenus,$children);
                        }
                    }

                    if($menu['id'] == end($childLastId)){
                        array_pop($childLastId);
                        echo '</ul>';
                    }
                    echo '</li>';
                }
                ?>
<!--                <li class="nav-item">-->
<!--                    <a class="nav-link" href="--><?php //echo Url::action('Content');?><!--"><i class="fa fa-cube"></i> 内容管理</a>-->
<!--                </li>-->
<!--                <li class="nav-item">-->
<!--                    <a class="nav-link" href="--><?php //echo Url::action('Catalog');?><!--"><i class="fa fa-square-poll-horizontal"></i> 栏目</a>-->
<!--                </li>-->
<!---->
<!--                <li class="nav-item dropdown">-->
<!--                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">-->
<!--                        <i class="fa fa-gear"></i> 设置</a>-->
<!--                    <ul class="dropdown-menu">-->
<!--                        <li><a class="dropdown-item" href="--><?php //echo Url::action('System@settings');?><!--">基础设置</a></li>-->
<!--                        <li><a class="dropdown-item" href="--><?php //echo Url::action('AdminMenu');?><!--">系统菜单设置</a></li>-->
<!---->
<!--                    </ul>-->
<!--                </li>-->
            </ul>


            <ul class="navbar-nav d-flex mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('/'); ?>" target="_blank">网站首页</a>
                </li>
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
