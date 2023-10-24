<?php
?>
<form>
    <input type="hidden" name="admin_id" value="<?php echo $user['id'] ?? 0; ?>" />
    <div class="row gx-3 gy-2 align-items-center" >
        <div class="col-sm-6">
            <label for="data_username" class="form-label">用户名</label>
            <input type="text" class="form-control" id="data_username" name="data[username]" value="<?php echo $user['username'];?>" placeholder="请输入6~20位用户名">
        </div>
        <div class="col-sm-6">
            <label for="data_full_name" class="form-label">姓名</label>
            <input type="text" class="form-control" id="data_full_name" name="data[full_name]" value="<?php echo $user['full_name'];?>" placeholder="">
        </div>
        <div class="col-sm-6">
            <label for="data_email" class="form-label">Email</label>
            <input type="email" class="form-control" id="data_email" name="data[email]" value="<?php echo $user['email'];?>" placeholder="">
        </div>
        <div class="col-sm-6">
            <label for="data_phone_number" class="form-label">手机号码</label>
            <input type="text" class="form-control" id="data_phone_number" name="data[phone_number]" value="<?php echo $user['phone_number'];?>" placeholder="">
        </div>
        <div class="col-sm-6">
            <label for="data_password" class="form-label">密码 <small class="text-black-50">留空不修改密码</small> </label>
            <input type="password" class="form-control" id="data_password" name="data[password]" value="" placeholder="请输入密码">
        </div>
        <div class="col-sm-6">
            <label for="data_new_password" class="form-label">再次输入新密码</label>
            <input type="password" class="form-control" id="data_new_password" name="data[new_password]" value="" placeholder="请输入新密码">
        </div>

        <div class="col-sm-6">
            <label for="catalog_show_position" class="form-label">用户角色</label><br/>
            <?php
            foreach ($roles as $row): ?>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="user_roles[]"  <?php echo in_array($row['role_id'],$user_roles) ? 'checked':'' ;?>
                           id="roles_<?php echo $row['role_id'];?>" value="<?php echo $row['role_id'];?>">
                    <label class="form-check-label" for="roles_<?php echo $row['role_id'];?>"><?php echo $row['name']; ?></label>
                </div>
            <?php endforeach; ?>


        </div>
        <div class="col-sm-6">
            <label for="data_status" class="form-label">状态</label>
            <select class="form-select" id="data_status" name="data[status]" >
                <?php foreach (\zap\User::getStatus() as $key => $title):
                    ?>
                    <option value="<?php echo $key;?>"  <?php echo $key == $user['status'] ?'selected':null;  ?> ><?php echo $title;?></option>
                <?php endforeach; ?>
            </select>

        </div>
    </div>






</form>
<script>

</script>