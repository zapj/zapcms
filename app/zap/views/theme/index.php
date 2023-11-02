<?php

use zap\NodeType;

!IS_AJAX && $this->layout('layouts/common');
?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 ">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><a href="<?php echo url_action('User') ?>">主题管理</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
<!--            <a href="#" class="btn btn-success btn-sm" ><i class="fa-solid fa-search"></i> 主题市场</a>-->

        </div>
    </div>

</nav>
<main class="container zap-main">
    <div class="row mt-3">
        <div class="col-12 mb-3 border-bottom"><h5 class=" pb-2 mb-0"><i class="fa-solid fa-wand-magic-sparkles"></i> 主题管理</h5></div>
        <?php foreach($themes as $theme){ ?>
            <div class="col-6 col-md-3 mb-2">
                <div class="card" >
                    <div class="theme-screenshot">
                        <img src="<?php echo base_url("/themes/{$theme['name']}/{$theme['screenshot']}") ?>" class="card-img-top" alt="...">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fs-6" ><?php echo $theme['title'] ?> </h5>
                        <p class="card-text text-black-50 " style="font-size: 12px;"><?php echo \zap\util\Str::truncate($theme['description'],100) ?> - 版本:<small><?php echo $theme['version'] ?></small></p>

                    </div>
                    <div class="card-footer ">
                        <?php if($website_options['website.theme'] === $theme['dirname']){ ?>
                            <a href="#" class="btn btn-outline-info btn-sm">主题设置</a>
                        <?php }else{ ?>
                            <button  class="btn btn-outline-success btn-sm" onclick="activationTheme('<?php echo $theme['dirname']; ?>')" type="button">启用主题</button>
                        <?php } ?>
                    </div>
                </div>

            </div>
        <?php } ?>

    </div>


</main>
<script>
    function activationTheme(themeName){
        $.ajax({
            url:'<?php echo url_action('Theme@activationTheme') ?>',
            method:'POST',
            dataType:'json',
            data:{theme:themeName},
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
