<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZAP</title>
    <link href="<?php echo base_url();?>/assets/bootstrap/5.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/admin/css/default.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/layer/theme/default/layer.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/fontawesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="<?php echo base_url();?>/assets/jquery/jquery-3.6.4.min.js"></script>
    <script src="<?php echo base_url();?>/assets/layer/layer.js"></script>
    <script>
        window.ZAP_BASE_URL = '<?php echo \zap\facades\Url::home(); ?>';
    </script>
    <?php
    print_styles();
    print_scripts();
    print_scripts(ASSETS_HEAD_TEXT);
    ?>
</head>

<body class="bg-body-tertiary">
        <?php $this->include('layouts/top_navs','top_navs');?>

        <?php echo $this->block('top_navs');?>
        <div id="zContent">
        <?php echo $this->block('content');?>
        </div>
        <footer class="pt-5 pb-2 text-body-secondary text-center text-small ">
            <p class="mb-1">© 2023 ZAP.CN</p>
<!--            <ul class="list-inline">-->
<!--                <li class="list-inline-item"><a href="#">Privacy</a></li>-->
<!--                <li class="list-inline-item"><a href="#">Terms</a></li>-->
<!--                <li class="list-inline-item"><a href="#">Support</a></li>-->
<!--            </ul>-->
        </footer>
        <div class="toast-container p-3 top-0 start-50 translate-middle-x" id="topCenterToast" data-original-class="toast-container p-3"></div>
        <div class="toast-container p-3 top-0 end-0" id="topRightToast" data-original-class="toast-container p-3"></div>
        <div class="toast-container p-3 top-50 start-50 translate-middle" id="centerToast" data-original-class="toast-container p-3"></div>
        <script src="<?php echo base_url();?>/assets/bootstrap/5.3.1/js/bootstrap.bundle.min.js" ></script>
        <script src="<?php echo base_url();?>/assets/admin/js/admin.js"></script>
        <?php
        print_scripts(ASSETS_BODY);
        print_scripts(ASSETS_BODY_TEXT);
        ?>
        <script>
            <?php
            \zap\AdminPage::instance()->showFlashMessages();
            ?>
        </script>
    </body>
</html>