<?php

use zap\cms\AdminMenu;
use zap\facades\Url;

?>
<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-primary" aria-label="Main navigation">
    <div class="container-fluid px-2 px-lg-3">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo Url::home();?>">
            <img class="me-2" src="<?php echo base_url();?>/assets/admin/img/zap_logo_white.svg" alt="ZAP" height="32">
            <span class="d-none d-sm-inline fw-bold"></span>
        </a>
        
        <!-- 移动端：用户信息快捷入口 -->
        <div class="d-flex d-lg-none align-items-center gap-2">
            <a href="<?php echo Url::action('User@profile'); ?>" class="text-white text-decoration-none">
                <i class="fa fa-user-circle"></i>
            </a>
            <button class="navbar-toggler p-1 border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>

        <!-- PC端导航 -->
        <div class="collapse navbar-collapse" id="navbarsTopMenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo Url::active('index/index');?>" aria-current="page" href="<?php echo Url::home();?>">
                        <i class="fa fa-dashboard me-1"></i><span>控制面板</span>
                    </a>
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
                            <i class="<?php echo $menu['icon'];?> me-1"></i><span><?php echo $menu['title'];?></span>
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
            </ul>

            <hr class="d-block d-lg-none opacity-25"/>
            <ul class="navbar-nav d-flex mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo base_url('/'); ?>" target="_blank">
                        <i class="fa fa-external-link-alt me-1"></i><span>网站首页</span>
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user-circle me-1"></i><span class="d-none d-md-inline"><?php echo $zapAdmin['username']; ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo Url::action('User@profile'); ?>">
                                <i class="fa fa-user-pen me-2"></i> 个人资料</a></li>
                        <li><a class="dropdown-item" href="<?php echo Url::action('User@changePassword'); ?>">
                                <i class="fa fa-key me-2"></i> 修改密码</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo Url::action('Auth@signOut'); ?>">
                                <i class="fa fa-sign-out-alt me-2"></i> 安全退出</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- 移动端侧边菜单 (Offcanvas) -->
<div class="offcanvas offcanvas-start bg-primary text-white" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header border-bottom border-light border-opacity-25">
        <div class="d-flex align-items-center">
            <img src="<?php echo base_url();?>/assets/admin/img/zap_logo_white.svg" alt="ZAP" height="32" class="me-2">
            <span class="fw-bold"><?php echo $zapAdmin['username']; ?></span>
        </div>
        <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <!-- 快捷操作 -->
        <div class="p-3 border-bottom border-light border-opacity-25">
            <div class="row g-2 text-center">
                <div class="col-4">
                    <a href="<?php echo Url::home();?>" class="text-white text-decoration-none d-flex flex-column align-items-center">
                        <i class="fa fa-dashboard fs-4 mb-1"></i>
                        <small>控制台</small>
                    </a>
                </div>
                <div class="col-4">
                    <a href="<?php echo Url::action('User@profile'); ?>" class="text-white text-decoration-none d-flex flex-column align-items-center">
                        <i class="fa fa-user-pen fs-4 mb-1"></i>
                        <small>资料</small>
                    </a>
                </div>
                <div class="col-4">
                    <a href="<?php echo base_url('/'); ?>" target="_blank" class="text-white text-decoration-none d-flex flex-column align-items-center">
                        <i class="fa fa-external-link-alt fs-4 mb-1"></i>
                        <small>网站</small>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- 主菜单 -->
        <div class="mobile-menu-list">
            <a class="menu-item <?php echo Url::active('index/index');?>" href="<?php echo Url::home();?>">
                <i class="fa fa-dashboard me-3"></i>控制面板
            </a>
            
            <?php
            $adminMenus = AdminMenu::instance()->getTreeArray();
            
            foreach($adminMenus as $menu){
                $hasChildren = !empty($menu['children']);
            ?>
            
            <?php if($hasChildren){ ?>
            <!-- 有子菜单的项 -->
            <div class="menu-group">
                <a class="menu-item menu-toggle" href="#menu-<?php echo $menu['id']; ?>" data-bs-toggle="collapse">
                    <i class="<?php echo $menu['icon'];?> me-3"></i>
                    <span><?php echo $menu['title']; ?></span>
                    <i class="fa fa-chevron-right ms-auto fa-sm"></i>
                </a>
                <div class="collapse submenu" id="menu-<?php echo $menu['id']; ?>">
                    <?php foreach($menu['children'] as $child){ ?>
                    <a class="menu-item submenu-item <?php echo Url::active($child['active_rule']);?>" href="<?php echo Url::action($child['link_to']);?>">
                        <i class="<?php echo $child['icon'];?> me-3"></i>
                        <?php echo $child['title']; ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } else { ?>
            <!-- 无子菜单的项 -->
            <a class="menu-item <?php echo Url::active($menu['active_rule']);?>" href="<?php echo Url::action($menu['link_to']);?>">
                <i class="<?php echo $menu['icon'];?> me-3"></i>
                <?php echo $menu['title']; ?>
            </a>
            <?php } ?>
            
            <?php } ?>
        </div>
        
        <!-- 底部操作 -->
        <div class="mt-auto p-3 border-top border-light border-opacity-25">
            <a class="menu-item text-danger" href="<?php echo Url::action('Auth@signOut'); ?>">
                <i class="fa fa-sign-out-alt me-3"></i>安全退出
            </a>
        </div>
    </div>
</div>

<style>
/* 移动端菜单样式 - Green Theme */
#mobileMenu {
    width: 280px;
    max-width: 85vw;
    background: linear-gradient(180deg, #10b981 0%, #059669 100%) !important;
}

#mobileMenu .offcanvas-header {
    padding: 1rem;
}

#mobileMenu .offcanvas-body {
    display: flex;
    flex-direction: column;
    padding: 0;
    overflow-y: auto;
}

.mobile-menu-list {
    flex: 1;
    overflow-y: auto;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 0.875rem 1rem;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
    transition: all 0.2s ease;
}

.menu-item:hover,
.menu-item.active {
    background: rgba(255, 255, 255, 0.15);
    color: #fff;
}

.menu-item.active {
    background: rgba(255, 255, 255, 0.2);
    border-left: 3px solid #fff;
    padding-left: calc(1rem - 3px);
}

/* 子菜单折叠按钮 */
.menu-toggle {
    cursor: pointer;
}

.menu-toggle .fa-chevron-right {
    transition: transform 0.2s ease;
}

.menu-toggle[aria-expanded="true"] .fa-chevron-right {
    transform: rotate(90deg);
}

/* 子菜单 */
.submenu {
    background: rgba(0, 0, 0, 0.15);
}

.submenu-item {
    padding-left: 2.5rem !important;
    font-size: 0.9rem;
}

.submenu-item:hover,
.submenu-item.active {
    background: rgba(255, 255, 255, 0.1);
}

/* 快捷操作区域 */
#mobileMenu .p-3.border-bottom {
    border-color: rgba(255, 255, 255, 0.2) !important;
}

#mobileMenu .border-top {
    border-color: rgba(255, 255, 255, 0.2) !important;
}

/* 触摸友好的尺寸 */
@media (max-width: 991px) {
    .menu-item {
        padding: 1rem 1.25rem;
        font-size: 0.95rem;
    }
    
    .submenu-item {
        padding: 0.75rem 1.25rem 0.75rem 2.75rem !important;
    }
}

/* 收起PC端导航在移动端 */
@media (max-width: 991px) {
    #navbarsTopMenu {
        display: none !important;
    }
}
</style>
