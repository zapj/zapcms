<?php use zap\facades\Url; ?>
<?php $this->layout('layouts/common'); ?>
<a href="<?= Url::action('Node/typesForm')?>" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg"></i> 添加模型
</a>

<div class="card">
    <div class="card-header">
        <form class="row g-2" method="get" action="<?= Url::action('Node/types')?>">
            <div class="col-auto">
                <input type="text" name="search" class="form-control form-control-sm"
                       placeholder="搜索标识 / 标题" value="<?=e($search ?? '')?>">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">全部状态</option>
                    <option value="1" <?=($status ?? '') === '1' ? 'selected' : ''?>>启用</option>
                    <option value="0" <?=($status ?? '') === '0' ? 'selected' : ''?>>禁用</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-search"></i> 搜索
                </button>
            </div>
            <div class="col-auto">
                <a href="<?= Url::action('Node/types')?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-repeat"></i> 重置
                </a>
            </div>
        </form>
    </div>

    <?php if (empty($data)): ?>
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox" style="font-size:3rem;display:block;"></i>
        <p class="mt-2">暂无内容模型数据，点击「添加模型」开始创建。</p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover table-sm mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:60px">ID</th>
                    <th>类型标识</th>
                    <th>标题</th>
                    <th>描述</th>
                    <th>处理类</th>
                    <th>版本</th>
                    <th class="text-center">排序</th>
                    <th class="text-center">状态</th>
                    <th class="text-end" style="width:140px">操作</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?=$row['type_id']?></td>
                    <td><code><?=e($row['type_name'])?></code></td>
                    <td><strong><?=e($row['title'])?></strong></td>
                    <td class="text-muted small"><?=e(mb_strlen($row['description'] ?? '') > 30 ? mb_substr($row['description'], 0, 30) . '…' : ($row['description'] ?? ''))?></td>
                    <td><small class="text-monospace"><?=e($row['node_type'] ?? '—')?></small></td>
                    <td><?=e($row['version'] ?? '0.0.0')?></td>
                    <td class="text-center"><?=$row['sort_order']?></td>
                    <td class="text-center">
                        <span class="badge bg-<?=$row['status'] ? 'success' : 'secondary'?>">
                            <?=$row['status'] ? '启用' : '禁用'?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="<?= Url::action('Node/typesForm', ['id' => $row['type_id']])?>"
                           class="btn btn-outline-primary btn-xs" title="编辑">
                            <i class="bi bi-pencil"></i> 编辑
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-xs"
                                onclick="deleteType(<?=$row['type_id']?>, '<?=e($row['title'])?>')" title="删除">
                            <i class="bi bi-trash"></i> 删除
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
function deleteType(id, title) {
    if (!confirm('确认删除模型「' + title + '」？此操作不可恢复。')) return;

    var formData = new FormData();
    formData.append('id', id);

    fetch('<?= Url::action('Node/typesDelete')?>', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.code === 0) {
            location.reload();
        } else {
            alert(res.msg || '删除失败');
        }
    })
    .catch(function() {
        alert('网络错误，请重试');
    });
}
</script>
