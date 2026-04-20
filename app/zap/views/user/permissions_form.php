<?php
?>
<form>
    <input type="hidden" name="perm_id" value="<?php echo $data['perm_id'] ?? 0; ?>" />
    <input type="hidden" name="data[pid]" value="<?php echo $pid ?? 0; ?>" />
    <div class="mb-3">
        <label for="data_name" class="form-label">权限名称</label>
        <input type="text" class="form-control" id="data_name" name="data[title]" value="<?php echo $data['title'];?>" placeholder="请输入权限名称" />
    </div>
    <div class="mb-3">
        <label for="data_perm_key" class="form-label">权限值</label>
        <input type="text" class="form-control" id="data_perm_key" name="data[perm_key]" value="<?php echo $data['perm_key'];?>" placeholder="权限值 controller/method" />
    </div>
    <div class="mb-3">
        <label for="data_extras" class="form-label">扩展</label>
        <input type="hidden" class="form-control" id="data_extras" name="data[extras]" value="<?php echo $data['extras'];?>"  />
        <div class="input-group mb-3">
            <span class="input-group-text">名称</span>
            <input type="text" class="form-control" placeholder="权限名称"  name="perm_value" id="perm_value">
            <span class="input-group-text">值</span>
            <input type="text" class="form-control" placeholder="权限值"  name="perm_key" id="perm_key">
            <button class="btn btn-outline-secondary" type="button" onclick="addExtras()">添加</button>
        </div>
        <div id="perm-extras-container" class="overflow-y-auto " style="max-height: 200px">
            <?php foreach ($extras as $key=>$title){ ?>
                <div class="input-group mb-3">
                    <span class="input-group-text">名称</span>
                    <input type="text" class="form-control" placeholder="请输入名称" name="extras[<?php echo $key;?>][title]" value="<?php echo $title;?>">
                    <span class="input-group-text">值</span>
                    <input type="text" class="form-control" placeholder="请输入值" name="extras[<?php echo $key;?>][key]" value="<?php echo $key;?>">
                    <button class="btn btn-outline-secondary" type="button" onclick="$(this).parent().remove()">删除</button>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="mb-3">
        <label for="data_description" class="form-label">权限描述</label>
        <input type="text" class="form-control" id="data_description" name="data[description]" value="<?php echo $data['description'];?>" placeholder="请输入权限描述" />
    </div>

</form>
<script>
    var permkey_index = <?php echo count($extras)+1; ?>;

    function addExtras(){
        const elPermKey = document.getElementById('perm_key');
        const elPermValue = document.getElementById('perm_value');
        const container = document.getElementById('perm-extras-container');
        const perm_key = elPermKey.value;
        const perm_value = elPermValue.value;
        if(perm_key==="" || perm_value === ""){
            return false;
        }
        $(container).prepend(`<div class="input-group mb-3">
            <span class="input-group-text">名称</span>
            <input type="text" class="form-control" placeholder="请输入名称" name="extras[${permkey_index}][title]" value="${perm_value}">
            <span class="input-group-text">值</span>
            <input type="text" class="form-control" placeholder="请输入值" name="extras[${permkey_index}][key]" value="${perm_key}">
            <button class="btn btn-outline-secondary" type="button" onclick="$(this).parent().remove()">删除</button>
        </div>`);
        permkey_index++;
        elPermValue.value = '';
        elPermKey.value = '';
    }
</script>
