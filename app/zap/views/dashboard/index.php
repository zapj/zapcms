<?php

use zap\NodeType;

$this->layout('layouts/common');
?>





<main class="container">
    <div class="row mt-3">
        <?php foreach($node_types_count as $type=>$count){
            ?>


        <div class="col">
            <div class="card">
                <div class="card-body text-center">
                    <a  href="<?php echo url_action("Node@{$type}"); ?>">
                    <strong class="text-success fw-bold fs-2"><?php echo $count ?></strong></a>
                    <br/>
                    <i class="fa fa-cogs"></i>
                    <?php echo NodeType::getTitle($type); ?>
                </div>
            </div>
        </div>
        <?php } ?>

    </div>


    <div class="my-3 p-3 bg-body rounded shadow-sm">
        <h6 class="border-bottom pb-2 mb-0"><i class="fa fa-info-circle"></i> 系统信息</h6>
        <div class="table-responsive">
            <table class="table table-bordered text-nowrap">
            <tbody>
            <tr>
                <td >ZapCMS 版本</td>
                <td>v<?php echo ZAP_CMS_VERSION; ?></td>
                <td>PHP 版本</td>
                <td><?php echo PHP_VERSION , '(' , php_sapi_name() , ')'; ?></td>
            </tr>
            <tr>
                <?php $driver = \zap\DB::getPDO()->getAttribute(PDO::ATTR_DRIVER_NAME) ;?>
                <td ><?php echo $driver;?> 版本</td>
                <td>v<?php
                    if($driver == 'mysql'){
                    echo \zap\DB::value("SELECT VERSION()");
                    }else if($driver == 'sqlite'){
                        echo join(',',\SQLite3::version());
                    } ?></td>
                <td>OpenSSL</td>
                <td><?php echo constant("OPENSSL_VERSION_TEXT") ?? '不支持'; ?></td>
            </tr>
            <tr >
                <td >PHP GD</td>
                <td  >
                    <?php  $gdInfo = gd_info(); echo current($gdInfo);
                    ?>
                </td>
                <td >PDO 驱动</td>
                <td  >
                    <?php   echo join('/',PDO::getAvailableDrivers());
                    ?>
                </td>
            </tr>
            <tr>
                <td >Web Server</td>
                <td colspan="3"><?php echo \zap\http\Request::server('SERVER_SOFTWARE'); ?></td>
            </tr>
            <tr>
                <td >操作系统</td>
                <td colspan="3"><?php echo php_uname(); ?></td>
            </tr>

            </tbody>
        </table>
        </div>
    </div>
</main>
