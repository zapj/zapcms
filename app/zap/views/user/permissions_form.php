<?php
use zap\Catalog;
use zap\NodeType;
?>
<form>
    <input type="hidden" name="perm_id" value="<?php echo $data['perm_id'] ?? 0; ?>" />
    <input type="hidden" name="data[pid]" value="<?php echo $pid ?? 0; ?>" />
    <div class="mb-3">
        <label for="data_name" class="form-label">权限名称</label>
        <input type="text" class="form-control" id="data_name" name="data[title]" value="<?php echo $data['title'];?>" placeholder="请输入权限名称" />
    </div>
    <div class="mb-3">
        <label for="data_slug" class="form-label">权限值</label>
        <input type="text" class="form-control" id="data_slug" name="data[slug]" value="<?php echo $data['slug'];?>" placeholder="权限值 controller/method" />
    </div>
    <div class="mb-3">
        <label for="data_extras" class="form-label">扩展</label>
        <input type="hidden" class="form-control" id="data_extras" name="data[extras]" value="<?php echo $data['extras'];?>"  />
        <div class="input-group mb-3">
            <span class="input-group-text">名称</span>
            <input type="text" class="form-control" placeholder="Key" aria-label="Key" name="perm_key" id="perm_key">
            <span class="input-group-text">值</span>
            <input type="text" class="form-control" placeholder="Value" aria-label="Value" name="perm_value" id="perm_value">
            <button class="btn btn-outline-secondary" type="button" onclick="addExtras()">添加</button>
        </div>
        <div id="perm-extras-container" class="overflow-y-auto container" style="max-height: 200px">

        </div>
    </div>
    <div class="mb-3">
        <label for="data_description" class="form-label">权限描述</label>
        <input type="text" class="form-control" id="data_description" name="data[description]" value="<?php echo $data['description'];?>" placeholder="请输入权限描述" />
    </div>

</form>
<script>
    function addExtras(){
        const perm_key = document.getElementById('perm_key').value;
        const perm_value = document.getElementById('perm_value').value;
        const container = document.getElementById('perm-extras-container');
        $(container).append(`
        <div class="row mb-3">
                <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">${perm_key}</label>
                <div class="col-sm-10">
                    ${perm_value}
                </div>
            </div>
        `);
    }
</script>
