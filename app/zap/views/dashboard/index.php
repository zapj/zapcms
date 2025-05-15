<?php

use zap\cms\NodeType;

$this->layout('layouts/common');
?>
<main class="container ">
    <div class="row mt-3">
        <div class="col-12"><h6 class=" pb-2 mb-0"><i class="fa fa-file-pen"></i> 单页管理</h6></div>
        <?php foreach($pages as $page){ ?>
        <div class="col-4 col-md-2 mb-2">
            <div class="card">
                <div class="card-body text-center">
                    <a  href="<?php echo url_action("Node@page/edit/{$page['id']}"); ?>"><i class="fa fa-file"></i>
                    <strong class="text-success"><?php echo $page['title'] ?></strong></a>


                </div>
            </div>
        </div>
        <?php } ?>

    </div>

    <div class="row mt-2 ">
        <div class="col-md-12 mb-2">
            <div class="card">
                <div class="card-body">
                    <h6 class=" pb-2 mb-0"><i class="fa fa-sitemap"></i> 网站信息</h6>
                    <div class="list-group list-group-horizontal ">
                        <?php foreach($node_types_statistics as $type=>$count){
                            ?>
                        <a class="list-group-item list-group-item-action" href="<?php echo url_action("Node@{$type}"); ?>">
                            <?php echo NodeType::getTitle($type); ?>: <strong class="text-success"><?php echo $count ?></strong>
                        </a>

                        <?php } ?>
                    </div>
                    <hr class="border-secondary"/>
                    <div class="list-group list-group-horizontal ">
                        <a class="list-group-item list-group-item-action" href="#">
                            系统语言 : <strong class="text-success"><?php echo req()->getPreferredLanguage(); ?></strong>
                        </a>
                        <a class="list-group-item list-group-item-action" href="#">
                            站点模版 : <strong class="text-success"><?php echo option('website.theme','basic'); ?></strong>
                        </a>

                        <a class="list-group-item list-group-item-action" href="#">
                            文件上传限制 : <strong class="text-success"><?php echo ini_get('upload_max_filesize'); ?></strong>
                        </a>
                        <a class="list-group-item list-group-item-action" href="#">
                            内存限制 : <strong class="text-success"><?php echo ini_get('memory_limit'); ?></strong>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 mb-2">
            <div class="card">
            <div class="card-body">
                <h6 class="border-bottom pb-2 mb-0"><i class="fa fa-info-circle"></i> 系统信息</h6>
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <tbody>
                        <tr>
                            <td >Zap CMS 版本</td>
                            <td>v<?php echo ZAP_CMS_VERSION,'-',ZAP_CMS_RELEASE_DATE; ?></td>
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
                                    $dbh = new \PDO('sqlite:memory:');
                                    if($dbh){
                                        echo $dbh->query('select sqlite_version()')->fetchColumn(0);
                                        $dbh = null;
                                    }else{
                                        echo '当前环境不支持sqlite3';
                                    }

                                } ?></td>
                            <td>OpenSSL</td>
                            <td><?php echo constant("OPENSSL_VERSION_TEXT") ?? '不支持'; ?></td>
                        </tr>
                        <tr >
                            <td >PHP GD扩展</td>
                            <td >
                                <?php  $gdInfo = gd_info(); echo current($gdInfo);
                                ?>
                            </td>
                            <td >PDO 驱动</td>
                            <td  >
                                <?php   echo join('/',PDO::getAvailableDrivers());?>
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
            </div>
        </div>
    </div>


</main>
