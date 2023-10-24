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
    <div class="mb-3">
        <label for="data_description" class="form-label">简介</label>
        <input type="text" class="form-control" id="data_description" name="data[description]" value="<?php echo $data['description'];?>" placeholder="请输入角色介绍" />
    </div>
    <hr/>

    <div class="mb-3 overflow-y-auto " style="height: auto">
        <label  class="form-label">权限</label>
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-nowrap">
                <thead class="bg-light sticky-top top-0">
                <tr class="table-secondary">
                    <th scope="col" style="width: 50px">
                        <label>
                            <input class="form-check-input" type="checkbox" onclick="Zap.CheckBox_CheckAll(this,'.sl_perm_list')"/>
                        </label>
                    </th>

                    <th scope="col">权限名称</th>
                    <th scope="col">扩展</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($permissions as $item){
                    ?>
                    <tr>
                        <td class="w-auto">

                                <input name="perms[]"
                                       value="<?php echo $item['perm_key']; ?>"
                                       class="form-check-input sl_perm_list" type="checkbox"
                                <?php if(isset($role_permissions[$item['perm_key']])){echo 'checked';} ?>
                                />

                        </td>


                        <td class="w-50">
                                <?php echo $item['title']; ?>
                                <small class="text-black-50">ID:<?php echo $item['perm_id']; ?></small>
                                <small class="text-black-50"><?php echo $item['perm_key']; ?></small>




                        </td>

                        <td >
                            <?php if(!empty($item['extras'])){
                                $extrasPerms = json_decode($item['extras'],true);
                                if(json_last_error() !== JSON_ERROR_NONE){$extrasPerms = [];}
                                ?>
                                <?php
                                foreach ($extrasPerms as $key=>$value){
                                    ?>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="extra_id_<?php echo $key;?>" value="<?php echo $key;?>" name="extras[<?php echo $item['perm_key'];?>][]"
                                            <?php if(isset($role_permissions[$item['perm_key']][$key])){echo 'checked';} ?>
                                        >
                                        <label class="form-check-label" for="extra_id_<?php echo $key;?>"><?php echo $value;?></label>
                                    </div>
                                    <?php
                                }
                                ?>
                            <?php } ?>
                            <?php
                            };
                            ?>
                        </td>

                    </tr>



                </tbody>
            </table>


        </div>
    </div>


    <div class="mb-3 overflow-y-auto " style="height: auto">
        <label  class="form-label">系统菜单</label>
        <div class="table-responsive">
            <table class="table table-hover text-nowrap">
                <thead class="bg-light sticky-top top-0">
                <tr class="table-secondary">
                    <th scope="col" style="width: 50px">
                        <label>
                            <input class="form-check-input" type="checkbox" onclick="Zap.CheckBox_CheckAll(this,'.sl_admin_menus')"/>
                        </label>
                    </th>

                    <th scope="col">菜单名称</th>
                    <th scope="col">链接地址</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($admin_menus as $item){
                    ?>
                    <tr>
                        <td class="w-auto">

                            <input name="perms[]"
                                   value="admin_menu_<?php echo $item['id']; ?>"
                                   class="form-check-input sl_admin_menus" type="checkbox"
                                <?php if(isset($role_permissions['admin_menu_'.$item['id']])){echo 'checked';} ?>
                            />

                        </td>


                        <td class="w-50">
                            <div style="padding-left: <?php echo $item['level']*0.9 ?>rem !important;">
                            <i class="<?php echo $item['icon']; ?>"></i>
                            <?php echo $item['title']; ?>
                            <small class="text-black-50">ID:<?php echo $item['id']; ?></small>
                            </div>
                        </td>

                        <td class="text-black-50"><?php echo $item['link_to']; ?></td>

                    </tr>

                    <?php
                };
                ?>


                </tbody>
            </table>


        </div>
    </div>

</form>
