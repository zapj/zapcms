<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 下午3:27
 * @lastModified 2023/12/27 下午3:27
 *
 */

use zap\cms\Asset;
use zap\facades\Url;
$this->layout('layouts/common');
?>
<?php
$success_status = '<span style="color:green">Yes</span>';
$error_status = '<span style="color:red">No</span>';
?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 zap-top-bar">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item "><a href="<?php echo Url::action('System@settings') ?>">系统管理</a></li>
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('System@sysInfo') ?>">数据库管理</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
        </div>
    </div>

</nav>
<form id="zapForm">

    <!--    <input type="hidden" name="option_autoload[website]" value="1" />-->
    <main class="container zap-main">

        <div class="card shadow-sm">

            <?php
            $driver = \zap\DB::getPDO()->driver;
            $conn_name = config('database.default');
            ?>
            <div class="card-body p-0" >
                <table class="table table-hover table-bordered text-nowrap">
                    <thead>
                    <tr>
                        <th colspan="2">数据库信息</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>数据库类型</th>
                        <td><?php echo $driver;?></td>
                    </tr>
                    <?php if($driver === 'sqlite'): ?>
                        <tr>
                            <th>数据库路径</th>
                            <td><?php echo config("database.connections.{$conn_name}.dsn");?></td>
                        </tr>
                        <tr>
                            <th>数据库大小</th>
                            <td><?php echo \zap\util\FileUtils::sizeOf(str_replace('sqlite:','',config("database.connections.{$conn_name}.dsn")),true); ?></td>
                        </tr>
                    <?php elseif($driver === 'mysql' || $driver === 'mariadb'): ?>
                        <tr>
                            <th>Host</th>
                            <td><?php echo config("database.connections.{$conn_name}.host");?></td>
                        </tr>
                        <tr>
                            <th>数据库名称</th>
                            <td><?php echo config("database.connections.{$conn_name}.dbname");?></td>
                        </tr>

                    <?php endif; ?>

                    </tbody>
                </table>


            </div>



        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-body p-0" >
                <table class="table table-hover table-bordered text-nowrap">
                    <thead>
                    <tr>
                        <th colspan="2">数据库表</th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <th>表名</th>
                        <?php if($driver !== 'sqlite'): ?>
                        <th>表大小</th>
                        <?php endif; ?>
                    </tr>
                        <?php $tables = \app\zap\cms\system\SysInfo::getDatabaseTableNames(); ?>
                        <?php foreach ($tables as $table): ?>
                            <tr>
                                <td><?php echo $table['name'];?></td>
                                <?php if($driver !== 'sqlite'): ?>
                                    <td></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>


            </div>
        </div>

    </main>
</form>
<script>

    $(function(){
        $('#zapForm').validate({ignore:''});

    })

    function save(){
        const zapForm = $('#zapForm');
        if (!zapForm.valid()) {
            ZapToast.alert('必填项不能为空', {bgColor: bgDanger, position: Toast_Pos_Center});
            return false;
        }
        const load = Zap.loadding('正在保存，请稍后');
        $.ajax({
            url: '<?php echo Url::current();?>',
            method: 'post',
            data: zapForm.serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center});
                } else {
                    ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            }
        }).always(function () {
            load.dispose()
        });
    }
</script>
