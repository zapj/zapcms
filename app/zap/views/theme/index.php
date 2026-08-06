<?php

!IS_AJAX && $this->layout('layouts/common');
$this->view->page_title = '主题管理';
?>
<div class="row g-3">
    <div class="col-12">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h2 class="card-title fs-6 mb-0">
                    <i class="fa fa-paint-brush me-2"></i>主题管理
                </h2>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <?php foreach ($themes as $theme): ?>
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="card card-outline card-secondary h-100">
                            <div class="position-relative">
                                <img src="<?php echo base_url("/themes/{$theme['name']}/{$theme['screenshot']}"); ?>"
                                     class="card-img-top" alt="<?php echo $theme['title']; ?>" style="aspect-ratio:16/10;object-fit:cover;">
                                <?php if ($website_options['website.theme'] === $theme['dirname']): ?>
                                <span class="badge bg-success position-absolute top-0 end-0 m-1">当前</span>
                                <?php endif; ?>
                            </div>
                            <div class="card-body p-2 d-flex flex-column">
                                <h5 class="card-title fs-6 mb-1 text-break"><?php echo $theme['title']; ?></h5>
                                <p class="card-text text-muted small mb-1">
                                    <?php echo \zap\util\Str::truncate($theme['description'], 80); ?>
                                </p>
                                <small class="text-muted mt-auto">v<?php echo $theme['version']; ?></small>
                            </div>
                            <div class="card-footer p-2">
                                <?php if ($website_options['website.theme'] === $theme['dirname']): ?>
                                <a href="<?php echo url_action('Theme@settings'); ?>"
                                   class="btn btn-outline-info btn-sm w-100">
                                    <i class="fa fa-cog me-1"></i>主题设置
                                </a>
                                <?php else: ?>
                                <button class="btn btn-outline-success btn-sm w-100"
                                        onclick="activationTheme('<?php echo $theme['dirname']; ?>')" type="button">
                                    <i class="fa fa-check me-1"></i>启用主题
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function activationTheme(themeName){
        const load = Zap.loadding('正在启用主题...');
        $.ajax({
            url:'<?php echo url_action('Theme@activationTheme'); ?>',
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
        }).always(function(){ load.dispose(); });
    }
</script>
