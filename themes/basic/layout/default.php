<?php

use zap\facades\Url;
$catalogMenu = page()->getCatalog();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <base href="<?php echo base_url();?>/themes/basic/">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo option('website.title'); ?></title>
    <link href="<?php echo base_url();?>/themes/basic/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/themes/basic/css/font-awesome.min.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/themes/basic/css/style.css" rel="stylesheet">
    <?php if(page()->isHome){ ?>
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
    <div class="top">
        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    <p> <?php echo option('website.address') ?></p>
                </div>
                <div class="col-sm-6">
                    <ul class="list-inline pull-right">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                        <li><a href="#"><i class="fa fa-envelope-o"></i></a></li>
                        <li><a href="tel:<?php echo option('website.tel') ?>"><i class="fa fa-phone"></i> <?php echo option('website.tel') ?></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <nav class="navbar navbar-default">
        <div class="container">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <i class="fa fa-bars"></i>
            </button>
            <a href="<?php echo base_url('/'); ?>" class="navbar-brand">
                <img src="img/zap_logo_green.svg" width="160px" alt="Post">
            </a>
            <div class="collapse navbar-collapse" id="bs-navbar-collapse">
                <ul class="nav navbar-nav main-navbar-nav">
                    <li class="active"><a href="<?php echo base_url('/'); ?>" title="">首页</a></li>
                    <?php
                    $childLastId = [];
                    $childLastSlug = [];
                    while($menu = array_shift($catalogMenu)){
                    ?>
                    <li class="nav-item <?php echo !empty($menu['children']) ? 'dropdown':''; ?>">
                        <a data-id="<?php echo $menu['id'];?>" href="<?php echo url_slug($childLastSlug,$menu['slug']);?>"
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
            <div class="col-md-3 col-sm-6 col-xs-12 fbox">
                <h4><?php echo option('website.title');?></h4>
                <p class="text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam congue lectus diam, sit amet cursus massa efficitur sed. </p>
                <ul class="list-inline">
                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                    <li><a href="#"><i class="fa fa-linkedin"></i></a></li>
                </ul>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 fbox">
                <h4>SERVICES</h4>
                <ul class="big">
                    <li><a href="#" title="">Title One</a></li>
                    <li><a href="#" title="">Title Two</a></li>
                    <li><a href="#" title="">Title Three</a></li>
                    <li><a href="#" title="">Title Four</a></li>
                    <li><a href="#" title="">Title Five</a></li>
                    <li><a href="#" title="">Title Six</a></li>
                    <li><a href="#" title="">Title Seven</a></li>
                    <li><a href="#" title="">Title Eight</a></li>
                </ul>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 fbox">
                <h4>CONTENT</h4>
                <ul class="big">
                    <li><a href="#" title="">Title One</a></li>
                    <li><a href="#" title="">Title Two</a></li>
                    <li><a href="#" title="">Title Three</a></li>
                    <li><a href="#" title="">Title Four</a></li>
                    <li><a href="#" title="">Title Five</a></li>
                    <li><a href="#" title="">Title Six</a></li>
                    <li><a href="#" title="">Title Seven</a></li>
                    <li><a href="#" title="">Title Eight</a></li>
                </ul>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12 fbox">
                <h4>CONTENT</h4>
                <p class="text">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <p><a href="tel:+902222222222"><span class="glyphicon glyphicon-earphone" aria-hidden="true"></span> +90 222 222 22 22</a></p>
                <p><a href="mailto:iletisim@agrisosgb.com"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span> mail@awebsitename.com</a></p>
            </div>
        </div>
    </div>
    <div id="copyright">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <p class="pull-left">&copy; <?php echo date('Y'),'   ',option('website.title'); ?></p>
                </div>
                <div class="col-md-8">
                    <ul class="list-inline navbar-right">
                        <li><a href="#">HOME</a></li>
                        <li><a href="#">MENU ITEM</a></li>
                        <li><a href="#">MENU ITEM</a></li>
                        <li><a href="#">MENU ITEM</a></li>
                        <li><a href="#">MENU ITEM</a></li>
                        <li><a href="#">MENU ITEM</a></li>
                    </ul>
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