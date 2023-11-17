<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

use zap\NodeType;
register_scripts(base_url('/assets/plugins/zapuploader.js'));
!IS_AJAX && $this->layout('layouts/common');
?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 ">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><a href="<?php echo url_action('theme') ?>">主题管理</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
            <!--            <a href="#" class="btn btn-success btn-sm" ><i class="fa-solid fa-search"></i> 主题市场</a>-->

        </div>
    </div>

</nav>
<form action="" method="post" id="zapForm">
<main class="container zap-main ">
    <div class="row mt-3">
        <div class="col-12 mb-3 border-bottom">
            <h5 class=" pb-2 mb-0">
                <i class="fa-solid fa-wand-magic-sparkles"></i> <?php echo $customSettings['title']; ?>
                <div class=" text-end" >
                    <button class="btn btn-success btn-sm" type="button" onclick="saveSettings()"><i class="fa-solid fa-save"></i> 保存</button>

                </div>
            </h5>

        </div>
        <?php foreach($customSettings['items'] as $i=>$item){ ?>
            <div class="<?php echo $item['class'] ?? 'col-6 col-md-3 mb-2' ?>">
                <?php echo $item['labelText']; ?>
                <?php if($item['type'] === 'imageSelect'){ ?>
                    <div class="card">
                        <div class="card-body">
                        <img src="<?php echo $item['value'] ?>" class="img-thumbnail rounded mx-auto d-block" id="setting_img<?php echo $i; ?>" style="max-height: 200px;" alt=""/>
                            <input type="hidden" name="settings[<?php echo $item['name'] ?>]" id="setting_input<?php echo $i ?>" value="<?php echo $item['value']; ?>" />
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-success" data-zap-toggle="image" data-zap-callback="" data-zap-target="#setting_img<?php echo $i; ?>|#setting_input<?php echo $i; ?>">选择图片</button>
                            <button class="btn btn-success" >删除</button>
                        </div>
                    </div>
                <?php }else if($item['type'] ==='text'){ ?>

                <?php } ?>
            </div>
        <?php } ?>

    </div>


</main>
</form>
<script>
    function saveSettings(){
        $.ajax({
            url:'<?php echo url_action('Theme@saveSettings') ?>',
            method:'POST',
            dataType:'json',
            data:$('#zapForm').serialize(),
            success:function(data){
                if(data.code === 0){
                    ZapToast.alert("主题设置成功",{bgColor:bgSuccess});
                    Zap.reload();
                }else{
                    ZapToast.alert("主题设置失败: "+data.msg,{bgColor:bgDanger});
                }
            }
        })
    }
</script>
