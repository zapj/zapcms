<?php defined('IN_ZAP_CMS') or die('No permission to access'); ?>
<div class="bread_area">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <?php \zap\cms\BreadCrumb::instance()->display(); ?>
            </div>
        </div>
    </div>
</div>
