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
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('System@sysInfo') ?>">服务器信息</a></li>
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


            <div class="card-body p-0" >
                <table class="table table-hover table-bordered text-nowrap">
                    <thead>
                    <tr>
                        <th colspan="2">服务器基本信息</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <th>ZapCMS</th>
                        <td><?php echo ZAP_CMS_VERSION,'-',ZAP_CMS_RELEASE_DATE;?></td>
                    </tr>

                    <tr>
                        <th>主机系统</th>
                        <td><?php echo PHP_OS;?></td>
                    </tr>
                    <tr>
                        <th>访问地址</th>
                        <td><?php echo $_SERVER['HTTP_HOST']; ?></td>
                    </tr>
                    <tr>
                        <th>主机名称</th>
                        <td><?php echo $_SERVER['SERVER_NAME']; ?></td>
                    </tr>

                    <tr>
                        <th>主机地址</th>
                        <td><?php echo $_SERVER['SERVER_ADDR']; ?></td>
                    </tr>
                    <tr>
                        <th>主机端口</th>
                        <td><?php echo $_SERVER['SERVER_PORT']; ?></td>
                    </tr>

                    <tr>
                        <th>WEB软件</th>
                        <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                    </tr>

                    <tr>
                        <th>PHP版</th>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>

                    <tr>
                        <th>数据库驱动</th>
                        <td>pdo_sqlite</td>
                    </tr>

                    <tr>
                        <th>允许上传文件</th>
                        <td><?php echo ini_get('file_uploads');?></td>
                    </tr>
                    <tr>
                        <th>文件上传限制</th>
                        <td><?php echo ini_get('upload_max_filesize');?></td>
                    </tr>

                    <tr>
                        <th>表单提交限制</th>
                        <td><?php echo ini_get('post_max_size');?></td>
                    </tr>

                    <tr>
                        <th>最大提交数量</th>
                        <td><?php echo ini_get('max_file_uploads');?></td>
                    </tr>

                    <tr>
                        <th>分配内存限制</th>
                        <td><?php echo ini_get('memory_limit');?></td>
                    </tr>

                    <tr>
                        <th>GD库支持</th>
                        <td><?php if_else_echo(function_exists('gd_info'),$success_status,$error_status); ?></td>
                    </tr>

                    <tr>
                        <th>Curl支持</th>
                        <td><?php if_else_echo(function_exists('curl_init'),$success_status,$error_status); ?></td>
                    </tr>

                    <tr>
                        <th>缓存模块支持</th>
                        <td>

                            APC：<?php if_else_echo(function_exists('apc_add'),$success_status,$error_status); ?><br/>
                            APCu：<?php if_else_echo(function_exists('apcu_add'),$success_status,$error_status); ?><br/>

                            OPcache：<?php if_else_echo(function_exists('opcache_get_configuration'),$success_status,$error_status); ?><br/>
                            Memcache： <?php if_else_echo(class_exists('Memcache'),$success_status,$error_status); ?><br/>
                            Memcached：<?php if_else_echo(class_exists('Memcached'),$success_status,$error_status); ?>
                        </td>
                    </tr>

                    <tr>
                        <th>已加载模块</th>
                        <td><?php echo join('<br/>',get_loaded_extensions()); ?> </td>
                    </tr>
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
