<?php

use zap\cms\AdminMenu;

?>
<form class="row g-3">
    <input name="menu_id" value="<?php echo $menu['id']; ?>" type="hidden" />
    <div class="col-md-6">
        <label for="data_title" class="form-label">菜单名称</label>
        <input type="text" class="form-control form-control-sm" name="zap_data[title]" id="data_title" value="<?php echo $menu['title']; ?>" placeholder="请输入系统菜单名称" >
    </div>
    <div class="col-md-6">
        <label for="data_icon" class="form-label">图标</label>
        <div class="input-group mb-3">
            <input type="text" class="form-control form-control-sm" placeholder="菜单ICON" aria-label="菜单ICON" aria-describedby="preview-icon"
                   name="zap_data[icon]" id="data_icon" value="<?php echo $menu['icon'] ?? 'fa fa-circle-notch';?>"
                   onclick="ZapFaIcons(['#preview-icon-i','#data_icon']);" >
            <span class="input-group-text" id="preview-icon"><i class="<?php echo $menu['icon'] ?? 'fa fa-circle-notch';?>" id="preview-icon-i"></i></span>
        </div>
    </div>

    <div class="col-md-6">
        <label for="data_pid" class="form-label">上级菜单 </label>
        <select name="zap_data[pid]" id="data_pid" class="form-select form-select-sm ">
            <option value="0"> - 无 -</option>
            <?php
            AdminMenu::instance()->forEachAll(function($row) use ($menu){
                ?>
                <option value="<?php echo $row['id'];?>"  <?php echo $menu['pid']==$row['id'] ?'selected':''; ?>
                <?php echo !empty($menu['path']) && \zap\util\Str::startsWith($row['path'],$menu['path']) ? 'disabled':null;  ?>
                >
                    <?php echo  str_repeat("&nbsp;&nbsp;",$row['level']-1) ?><?php echo $row['title'];?></option>
                <?php
            });
            ?>

        </select>

    </div>
    <div class="col-md-6">
        <label for="data_show_position" class="form-label">显示位置</label><br/>
        <?php
        $positions = explode(',',$menu['show_position']);
        foreach (AdminMenu::getPositions() as $id => $title): ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="zap_data[show_position][]"  <?php echo in_array($id,$positions) ? 'checked':'' ;?>
                       id="data_show_position<?php echo $id;?>" value="<?php echo $id;?>">
                <label class="form-check-label" for="data_show_position<?php echo $id;?>"><?php echo $title;?></label>
            </div>
        <?php endforeach; ?>


    </div>

    <div class="col-md-6">
        <label for="data_link_to" class="form-label">链接地址</label>
        <input type="text" class="form-control form-control-sm" name="zap_data[link_to]" id="data_link_to" value="<?php echo $menu['link_to'];?>">
    </div>
    <div class="col-md-6">
        <label for="data_link_type" class="form-label">链接类型</label>
        <select name="zap_data[link_type]" class="form-select form-select-sm ">
            <option value="action" <?php echo $menu['link_type']=='action'?'selected':null;?> >Action</option>
            <option value="route" <?php echo $menu['link_type']=='route'?'selected':null;?>>Route</option>
            <option value="link" <?php echo $menu['link_type']=='link'?'selected':null;?>>自定义链接</option>
        </select>
    </div>
    <div class="col-md-6">
        <label for="data_link_target" class="form-label">链接目标</label>
        <select name="zap_data[link_target]" class="form-select form-select-sm ">
            <option value="_self">当前页面</option>
            <option value="_blank">新页面</option>
            <option value="_top">Top</option>
        </select>
    </div>

    <div class="col-md-6">
        <label for="data_active_rule" class="form-label">选中规则</label>
        <input type="text" class="form-control form-control-sm" name="zap_data[active_rule]" id="data_active_rule" value="<?php echo $menu['active_rule'];?>">
    </div>
    <div class="col-md-6">
        <label for="data_sort_order" class="form-label">排序</label>
        <input type="text" class="form-control form-control-sm" name="zap_data[sort_order]" id="data_sort_order" value="<?php echo $menu['sort_order'] ?? 0; ?>">
    </div>
</form>
<script>
    var CATALOG_PID = <?php echo isset($parent['id']) ? $parent['id'] : 0;?>;

</script>