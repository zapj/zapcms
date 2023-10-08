<?php
$this->layout('layouts/common');
?>





<main class="container">



    <div class="my-3 p-3 bg-body rounded shadow-sm">
        <h6 class="border-bottom pb-2 mb-0"><i class="fa fa-info-circle"></i> 系统信息</h6>
        <div class="table-responsive">
            <table class="table table-bordered text-nowrap">
            <tbody>
            <tr>
                <th >ZapCMS 版本</th>
                <td>v<?php echo \zap\Version::ZAPCMS_VERSION; ?></td>
                <td>PHP 版本</td>
                <td><?php echo PHP_VERSION , '(' , php_sapi_name() , ')'; ?></td>
            </tr>
            <tr>
                <th >MySQL 版本</th>
                <td>v<?php echo \zap\DB::value("SELECT VERSION()"); ?></td>
                <td>OpenSSL</td>
                <td><?php echo constant("OPENSSL_VERSION_TEXT") ?? '不支持'; ?></td>
            </tr>
            <tr >
                <th >PHP 扩展</th>
                <td colspan="3" >
                    <div class="overflow-y-auto" style="height: 200px">
                    <?php $gdInfo = gd_info(); ?>
                    <table class="table">
                        <tbody>
                       <?php foreach ($gdInfo as $name=>$value){ ?>
                        <tr>
                            <td ><?php echo $name ?></td>
                            <td ><?php if(is_string($gdInfo[$name])){
                                echo $gdInfo[$name];
                                }else{
                                echo $gdInfo[$name] ? 'yes' : 'no';

                                }?></td>

                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </td>
            </tr>
            <tr>
                <th >Web Server</th>
                <td colspan="3"><?php echo \zap\http\Request::server('SERVER_SOFTWARE'); ?></td>
            </tr>
            <tr>
                <th >操作系统</th>
                <td colspan="3"><?php echo php_uname(); ?></td>
            </tr>

            </tbody>
        </table>
        </div>
    </div>
</main>
