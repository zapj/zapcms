<?php use zap\facades\Url; ?>
<?php $this->layout('layouts/common'); ?>
<?php
$configs  = $configs ?? [];
$defaults = \zapcms\services\NodeType::CONFIG_DEFAULTS;
$inputTypes = [
    'list_per_page'      => ['label' => '列表分页显示数量', 'hint' => '该模型列表页每页显示的内容条数'],
    'list_image_width'   => ['label' => '列表图片宽度',     'hint' => '列表页缩略图宽度（px）'],
    'list_image_height'  => ['label' => '列表图片高度',     'hint' => '列表页缩略图高度（px）'],
    'detail_image_width' => ['label' => '详情页图片宽度',   'hint' => '文章/产品详情页内图片宽度（px）'],
    'detail_image_height'=> ['label' => '详情页图片高度',   'hint' => '文章/产品详情页内图片高度（px）'],
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1"><i class="fa fa-sliders-h"></i> 内容模型显示配置</h4>
        <div class="text-muted">按内容模型配置列表分页数量与图片尺寸，保存后前台列表页与详情页生效。</div>
    </div>
    <a href="<?= Url::action('Node/types')?>" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left"></i> 返回内容模型
    </a>
</div>

<form id="configForm" method="post" action="<?= Url::action('Node/typesConfigSave')?>">
    <?php foreach ($types as $type): ?>
    <?php
        $typeName = (string)$type['type_name'];
        $cfg = $configs[$typeName] ?? $defaults;
        $isDisabled = (int)$type['status'] === 0;
    ?>
    <div class="card mb-3">
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <span class="fw-semibold">
                <i class="fa fa-cube"></i> <?= e($type['title'])?>
                <code class="ms-2 small"><?= e($typeName)?></code>
                <?php if ($isDisabled): ?>
                    <span class="badge bg-secondary ms-2">已禁用</span>
                <?php endif; ?>
            </span>
            <span class="text-muted small"><?= e($type['description'] ?? '')?></span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach ($inputTypes as $key => $meta): ?>
                <?php $value = $cfg[$key] ?? $defaults[$key]; ?>
                <div class="col-md-6 col-lg-4">
                    <label class="form-label d-block">
                        <?= $meta['label']?>
                        <span class="text-muted small">(默认 <?= (int)$defaults[$key]?>)</span>
                    </label>
                    <input type="number" min="1" step="1"
                           name="config[<?= e($typeName)?>][<?= $key?>]"
                           value="<?= (int)$value?>" class="form-control">
                    <div class="form-text"><?= $meta['hint']?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save"></i> 保存全部配置
        </button>
        <button type="button" class="btn btn-light" onclick="history.back()">取消</button>
    </div>
</form>

<script>
(function(){
    $('#configForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        $.post($form.attr('action'), $form.serialize(), function(res){
            if(res && res.code === 0){
                zap.alert('保存成功', 'success');
            }else{
                zap.alert((res && res.msg) || '保存失败', 'error');
            }
        }, 'json').fail(function(){
            zap.alert('网络错误，请重试', 'error');
        });
    });
})();
</script>
