<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

register_scripts(base_url('/assets/plugins/zapuploader.js'));
!IS_AJAX && $this->layout('layouts/common');
$this->view->page_title = '主题设置';
?>

<div class="row g-3">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h2 class="card-title fs-6 mb-0">
                    <i class="fa fa-sliders-h me-2"></i>主题设置
                </h2>
            </div>
            <div class="card-body">
                <?php
                $view = theme_path('zap/settings.php');
                if (is_file($view)) {
                    include $view;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
    function saveSettings() {
        const load = Zap.loadding('正在保存...');
        $.ajax({
            url: '<?php echo url_action('Theme@saveSettings'); ?>',
            method: 'POST',
            dataType: 'json',
            data: $('#zapForm').serialize(),
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert("主题设置成功", {bgColor: bgSuccess});
                    Zap.reload({callback: typeof PageReload === 'undefined' ? function(){} : PageReload});
                } else {
                    ZapToast.alert("主题设置失败: " + data.msg, {bgColor: bgDanger});
                }
            }
        }).always(function(){ load.dispose(); });
    }
</script>
