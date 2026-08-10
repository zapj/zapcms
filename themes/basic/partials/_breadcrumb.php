<?php defined('IN_ZAPCMS') or die('No permission to access'); ?>
<div class="bread_area">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <?php \zapcms\services\BreadCrumb::instance()->display(); ?>
            </div>
        </div>
    </div>
</div>
