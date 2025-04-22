<?php
use zap\facades\Url;
$catalogMenu = pageState()->getCatalogList();
$footerCatalogMenu = $catalogMenu;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo option('website.title'); ?></title>
    <link href="<?php echo base_url();?>/themes/basic/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/themes/basic/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/themes/basic/css/style.css" rel="stylesheet">
    <?php if(pageState()->isHome){ ?>
    <link href="<?php echo base_url();?>/themes/basic/css/swiper-bundle.min.css" rel="stylesheet" >
    <script src="<?php echo base_url();?>/themes/basic/js/swiper-bundle.min.js"></script>
    <?php } ?>
    <style>

    </style>
    <!--[if lt IE 9]>
    <script src="<?php echo base_url();?>/themes/basic/js/html5shiv.min.js"></script>
    <script src="<?php echo base_url();?>/themes/basic/js/respond.min.js"></script>
    <![endif]-->
    <?php echo option('website.head_script'); ?>
</head>
<body>
<header class="site-header">
    <form action="<?php echo site_url('/search') ?>">
    <div class="top hidden-xs">
        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    <p> <?php echo option('website.address') ?></p>
                </div>
                <div class="col-sm-6">
                    <ul class="list-inline pull-right">
                        <li><input autocomplete="off" type="text" name="q" value="" placeholder="站内搜索" />
                        <input type="submit" value="搜索" /></li>
                        <li><a href="tel:<?php echo option('website.tel') ?>"><i class="fa fa-phone"></i> <?php echo option('website.tel') ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </form>
    <nav class="navbar navbar-default">
        <div class="container">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <i class="fa fa-bars"></i>
            </button>
            <a href="<?php echo home_url(); ?>" class="navbar-brand">
                <img src="<?php echo themes_url('basic');?>/img/zap_logo_green.svg" width="160px" alt="ZAP CMS Logo">
            </a>
            <div class="collapse navbar-collapse" id="bs-navbar-collapse">
                <ul class="nav navbar-nav main-navbar-nav">
                    <li <?php echo pageState()->isHome ? 'class="active"':null; ?> ><a href="<?php echo base_url('/'); ?>" title="">首页</a></li>
                    <?php
                    $childLastId = [];
                    $childLastSlug = [];
                    while($menu = array_shift($catalogMenu)){
                    ?>
                    <li class="nav-item <?php echo !empty($menu['children']) ? 'dropdown':''; ?> <?php echo ($menu['id']===pageState()->nodeId||pageState()->nodeId===$menu['link_object']) ?'active': ''; ?>">
                        <a data-id="<?php echo $menu['id'];?>" href="<?php echo url_slug($childLastSlug,$menu['slug']==='--zap-link-url'?$menu['link_to']:$menu['slug']);?>" title="<?php echo $menu['title']; ?>"
                            <?php if(!empty($menu['children'])) {
                                echo 'class="nav-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"';
                            }else if(count($childLastId)){
                                echo 'class="dropdown-item"';
                            }else{
                                echo 'class="nav-link "';
                            } ?>
                        >

                            <?php echo $menu['title'];?>  <?php if(!empty($menu['children'])) { echo '<span class="navbar-right-btn" ><span class="caret"></span></span>';} ?>
                        </a>

                        <?php
                        if(!empty($menu['children'])){
                            echo '<ul class="dropdown-menu">';
                            $childLastId[] = end($menu['children'])['id'];
                            $childLastSlug[] = $menu['slug'];
                            while($children = array_pop($menu['children'])){
                                array_unshift($catalogMenu,$children);
                            }
                        }

                        if($menu['id'] == end($childLastId)){
                            array_pop($childLastId);
                            array_pop($childLastSlug);
                            echo '</ul>';
                        }
                        echo '</li>';
                        }
                        ?>


                </ul>
            </div><!-- /.navbar-collapse -->
        </div>
    </nav>
</header>
<?php echo $this->block('content'); ?>
<footer class="site-footer">
    <div class="container">
        <div class="row">

            <?php foreach($footerCatalogMenu as $menu){
                if(!in_array(4,explode(',',$menu['show_position']))){
                    continue;
                }
                ?>
            <div class="col-md-3 col-sm-6 col-xs-12 fbox">
                <h4><?php echo $menu['title']; ?></h4>

                <ul class="big">
                    <?php foreach ($menu['children'] as $child){
                        if(!in_array(4,explode(',',$menu['show_position']))){
                            continue;
                        }
                        ?>
                    <li><a href="<?php echo url_slug($childLastSlug,$menu['slug']==='--zap-link-url'?$menu['link_to']:$menu['slug']);?>" title=""><?php echo $child['title'];?></a></li>
                    <?php } ?>

                </ul>
            </div>
            <?php } ?>

            <div class="col-md-3 col-sm-6 col-xs-12 fbox">
                <h4><?php echo option('website.title');?></h4>
                <p class="text"></p>
                <p><a href="tel:<?php echo option('website.tel');?>"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span> <?php echo option('website.tel');?></a></p>
                <p><a href="mailto:<?php echo option('website.email');?>"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> <?php echo option('website.email');?></a></p>
            </div>
        </div>
    </div>
    <div id="copyright">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                  &copy; <?php echo date('Y'),'   ',option('website.copyright'); ?>  <a href="https://www.zap.cn/zapcms">Powered by ZapCMS</a>
                </div>

            </div>
        </div>
    </div>
</footer>
<script src="<?php echo base_url();?>/assets/jquery/jquery-3.6.4.min.js"></script>
<script src="<?php echo base_url();?>/themes/basic/js/bootstrap.min.js"></script>

<?php
print_scripts(ASSETS_BODY);
print_scripts(ASSETS_BODY_TEXT);
?>

<?php echo option('website.foot_script'); ?>
<script>
    $(document).ready(function(){
        $('.navbar-right-btn').on('click',function(e){
            e.preventDefault();
            e.stopPropagation();
            console.log($(e.target).closest('li').toggleClass('open'))
        });
    });
</script>
</body>
</html>