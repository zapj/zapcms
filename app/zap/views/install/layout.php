
<!doctype html>
<html lang="zh" data-bs-theme="auto">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ZAP CMS INSTALL</title>


    <?php
    print_styles();
    print_scripts();
    print_scripts(ASSETS_HEAD_TEXT);
    ?>
</head>
<body class="bg-body-tertiary">

<?php echo $this->block('content');?>


<?php
print_scripts(ASSETS_BODY);
print_scripts(ASSETS_BODY_TEXT);
?>
</body>
</html>
