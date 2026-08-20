<?php
defined('IN_ZAPCMS') or die('No permission to access');

$state = pageState();
$catalogMenu = $state->getCatalogList();
$footerCatalogMenu = $catalogMenu;
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php echo $state->printMeta(); ?>
    <link href="<?php echo theme_url('css/bootstrap.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo theme_url('css/font-awesome.min.css'); ?>" rel="stylesheet">
    <link href="<?php echo theme_url('css/style.css'); ?>" rel="stylesheet">
    <?php if ($state->isHome): ?>
    <link href="<?php echo theme_url('css/swiper-bundle.min.css'); ?>" rel="stylesheet">
    <script src="<?php echo theme_url('js/swiper-bundle.min.js'); ?>"></script>
    <?php endif; ?>
    <link href="<?php echo theme_url('css/page-styles.css'); ?>" rel="stylesheet">
    <script src="<?php echo base_url(); ?>/assets/jquery/jquery-3.6.4.min.js"></script>
    <!--[if lt IE 9]>
    <script src="<?php echo theme_url('js/html5shiv.min.js'); ?>"></script>
    <script src="<?php echo theme_url('js/respond.min.js'); ?>"></script>
    <![endif]-->

    <?php echo option('website.head_script'); ?>
</head>
<body>

<header class="site-header">
    <form action="<?php echo append_url_suffix(site_url('/search')); ?>">
    <div class="top hidden-xs">
        <div class="container">
            <div class="row">
                <div class="col-sm-6">
                    
                </div>
                <div class="col-sm-6">
                    <ul class="list-inline pull-right">
                        <li>
                            <input autocomplete="off" type="text" name="q" value="" placeholder="站内搜索" />
                            <input type="submit" value="搜索" />
                        </li>
                        <li>
                            <a href="tel:<?php echo htmlspecialchars(option('website.tel')); ?>">
                                <i class="fa fa-phone"></i> <?php echo htmlspecialchars(option('website.tel')); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </form>

    <?php echo $this->partial('partials/_navbar', ['catalogMenu' => $catalogMenu]); ?>
</header>

<?php echo $this->block('content'); ?>

<?php echo $this->partial('partials/_footer', ['footerCatalogMenu' => $footerCatalogMenu]); ?>


<script src="<?php echo theme_url('js/bootstrap.min.js'); ?>"></script>

<?php
print_scripts(ASSETS_BODY);
print_scripts(ASSETS_BODY_TEXT);
?>

<?php echo option('website.foot_script'); ?>
<script>
$(function () {
    $('.navbar-right-btn').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(e.target).closest('li').toggleClass('open');
    });

    var $btn = $('.back-to-top');
    $(window).scroll(function () {
        $btn.toggleClass('show', $(this).scrollTop() > 300);
    });
    $btn.click(function () {
        $('html, body').animate({ scrollTop: 0 }, 600);
        return false;
    });
});
</script>

<div class="back-to-top" style="display:none;">
    <i class="fa fa-chevron-up"></i>
</div>

</body>
</html>
