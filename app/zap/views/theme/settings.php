<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

register_scripts(base_url('/assets/plugins/zapuploader.js'));
!IS_AJAX && $this->layout('layouts/common');
?>

<?php

 $view = theme_path('zap/settings.php');
 if(is_file($view)){
     include $view;
 }
?>
<script>

    function saveSettings() {
        $.ajax({
            url: '<?php echo url_action('Theme@saveSettings') ?>',
            method: 'POST',
            dataType: 'json',
            data: $('#zapForm').serialize(),
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert("主题设置成功", {bgColor: bgSuccess});
                    Zap.reload({callback: typeof PageReload === 'undefined' ? function(){} : PageReload  });
                } else {
                    ZapToast.alert("主题设置失败: " + data.msg, {bgColor: bgDanger});
                }
            }
        })
    }
</script>
