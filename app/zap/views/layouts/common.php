<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ZAP</title>
    <link href="<?php echo base_url();?>/assets/bootstrap/5.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/admin/css/default.css" rel="stylesheet">
    <link href="<?php echo base_url();?>/assets/fontawesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="<?php echo base_url();?>/assets/jquery/jquery-3.7.1.min.js"></script>

</head>

<body class="bg-body-tertiary">
<?php $this->include('layouts/top_navs','top_navs');?>
        <?php echo $this->block('top_navs');?>
        <?php echo $this->block('content');?>
        <footer class="my-5 pt-5 text-body-secondary text-center text-small">
            <p class="mb-1">© 2023 ZAP.CN</p>
<!--            <ul class="list-inline">-->
<!--                <li class="list-inline-item"><a href="#">Privacy</a></li>-->
<!--                <li class="list-inline-item"><a href="#">Terms</a></li>-->
<!--                <li class="list-inline-item"><a href="#">Support</a></li>-->
<!--            </ul>-->
        </footer>
        <div class="toast-container p-3 top-0 start-50 translate-middle-x" id="topToast" data-original-class="toast-container p-3"></div>
        <div class="toast-container p-3 top-0 end-0" id="topRightToast" data-original-class="toast-container p-3"></div>
        <script src="<?php echo base_url();?>/assets/bootstrap/5.3.1/js/bootstrap.bundle.min.js" ></script>
        <script src="<?php echo base_url();?>/assets/admin/js/admin.js"></script>
    </body>
</html>