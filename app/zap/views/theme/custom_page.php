<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

register_scripts(base_url('/assets/plugins/zapuploader.js'));
!IS_AJAX && $this->layout('layouts/common');
$this->view->page_title = $page;
?>

<div class="row g-3">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-body">
                <?php
                $themePage = theme_path("zap/{$page}.php");
                if (is_file($themePage)) {
                    include $themePage;
                }
                ?>
            </div>
        </div>
    </div>
</div>
