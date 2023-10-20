<?php
use zap\Catalog;
use zap\NodeType;
?>
<form>
    <input type="hidden" name="catalog_id" value="<?php echo $catalog['id'] ?? 0; ?>" />
    <div class="mb-3">
        <label for="data_username" class="form-label">用户名</label>
        <input type="text" class="form-control" id="data_username" name="data[username]" value="<?php echo $data['username'];?>" placeholder="请输入6~20位用户名">
    </div>
    <div class="mb-3">
        <label for="data_password" class="form-label">密码</label>
        <input type="text" class="form-control" id="data_password" name="data[password]" value="" placeholder="请输入密码">
    </div>
    <div class="mb-3">
        <label for="data_new_password" class="form-label">再次输入新密码</label>
        <input type="text" class="form-control" id="data_new_password" name="data[new_password]" value="" placeholder="请输入新密码">
    </div>
    <div class="mb-3">
        <label for="data_full_name" class="form-label">姓名</label>
        <input type="text" class="form-control" id="data_full_name" name="data[full_name]" value="<?php echo $data['full_name'];?>" placeholder="">
    </div>
    <div class="mb-3">
        <label for="data_status" class="form-label">状态</label>
        <select class="form-select" id="data_status" name="data[status]" >
            <?php foreach (\zap\User::getStatus() as $key => $title):
                ?>
                <option value="<?php echo $key;?>"  <?php echo $key == $data['status'] ?'selected':null;  ?> ><?php echo $title;?></option>
            <?php endforeach; ?>
        </select>

    </div>

    <div class="mb-3">
        <label for="catalog_show_position" class="form-label">用户角色</label><br/>
            <?php
            $positions = explode(',',$catalog['show_position'] );
            foreach (Catalog::getPositions() as $id => $title): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="catalog[show_position][<?php echo $id; ?>]"  <?php echo in_array($id,$positions) ? 'checked':'' ;?>
                           id="catalog_show_position<?php echo $id;?>" value="<?php echo $id;?>">
                    <label class="form-check-label" for="catalog_show_position<?php echo $id;?>"><?php echo $title;?></label>
                </div>
            <?php endforeach; ?>


    </div>


</form>
<script>
    var CATALOG_PID = <?php echo isset($parent['id']) ? $parent['id'] : 0;?>;

</script>