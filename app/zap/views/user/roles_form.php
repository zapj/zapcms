<?php
use zap\Catalog;
use zap\NodeType;
?>
<form>
    <input type="hidden" name="role_id" value="<?php echo $data['role_id'] ?? 0; ?>" />
    <div class="mb-3">
        <label for="data_name" class="form-label">角色名称</label>
        <input type="text" class="form-control" id="data_name" name="data[name]" value="<?php echo $data['name'];?>" placeholder="请输入角色名称" />
    </div>

</form>
