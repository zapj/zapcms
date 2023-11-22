<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

register_scripts(base_url('/assets/plugins/zapuploader.js'));
!IS_AJAX && $this->layout('layouts/common');
?>

<?php
 $page = theme_path("zap/{$page}.php");
 if(is_file($page)){
     include $page;
 }
?>

