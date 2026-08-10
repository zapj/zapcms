<?php use zap\facades\Url; ?>
<?php $this->layout('layouts/common'); ?>
<?php
$isEdit = !empty($row);
$typeName = $row['type_name'] ?? '';
$title   = $row['title'] ?? '';
$desc    = $row['description'] ?? '';
$class   = $row['node_type'] ?? '';
$version = $row['version'] ?? '0.0.0';
$sort    = $row['sort_order'] ?? 0;
$status  = $row['status'] ?? 1;
?>

<a href="<?= Url::action('Node/types')?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left"></i> 返回列表
</a>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form id="typeForm" method="post" action="<?= Url::action('Node/typesSave')?>">
                    <input type="hidden" name="type_id" value="<?=(int)($id ?? 0)?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">类型标识 <span class="text-danger">*</span></label>
                            <input type="text" name="type_name" value="<?=e($typeName)?>"
                                   class="form-control" placeholder="如: article, product"
                                   required maxlength="50" pattern="[a-z][a-z0-9_]*"
                                   <?=$isEdit ? 'readonly' : ''?>>
                            <div class="form-text">仅允许小写字母、数字、下划线，以字母开头。</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">显示名称 <span class="text-danger">*</span></label>
                            <input type="text" name="title" value="<?=e($title)?>"
                                   class="form-control" placeholder="如: 文章、商品"
                                   required maxlength="100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">描述</label>
                        <textarea name="description" rows="2" class="form-control"
                                  placeholder="简要描述该内容模型的用途"><?=e($desc)?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">处理类</label>
                            <input type="text" name="node_type" value="<?=e($class)?>"
                                   class="form-control" placeholder="如: \zapcms\node\ArticleNodeType"
                                   maxlength="200">
                            <div class="form-text">PHP 类名，用于自定义节点行为，留空使用默认处理。</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">版本</label>
                            <input type="text" name="version" value="<?=e($version)?>"
                                   class="form-control" placeholder="0.0.0" maxlength="20">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">排序</label>
                            <input type="number" name="sort_order" value="<?=$sort?>"
                                   class="form-control" min="0" step="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">状态</label>
                            <select name="status" class="form-select">
                                <option value="1" <?=$status == 1 ? 'selected' : ''?>>启用</option>
                                <option value="0" <?=$status == 0 ? 'selected' : ''?>>禁用</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> 保存
                        </button>
                        <a href="<?= Url::action('Node/types')?>" class="btn btn-outline-secondary">取消</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('typeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    var btn = this.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> 保存中…';

    var formData = new FormData(this);

    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.code === 0) {
            alert('保存成功');
            location.href = '<?= Url::action('Node/types')?>';
        } else {
            alert(res.msg || '保存失败');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg"></i> 保存';
        }
    })
    .catch(function() {
        alert('网络错误，请重试');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> 保存';
    });
});
</script>
