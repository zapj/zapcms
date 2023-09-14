<?php
use zap\AdminMenu;
?>
<form class="row g-3">
    <div class="col-md-12">
        <label for="data_title" class="form-label">菜单名称</label>
        <input type="text" class="form-control" name="zap_data[title]" id="data_title">
    </div>
    <div class="col-md-12">
        <label for="data_sort_order" class="form-label">排序</label>
        <input type="text" class="form-control" name="zap_data[sort_order]" id="data_sort_order">
    </div>
    <div class="col-md-6">
        <label for="data_pid" class="form-label">上级菜单 </label>
        <select name="zap_data[pid]" class="form-select form-select-sm ">
            <option value="0"> - 无 -</option>
            <?php
            AdminMenu::instance()->forEachAll(function($row){
                ?>
                <option value="<?php echo $row['id'];?>"><?php echo  str_repeat("&nbsp;&nbsp;",$row['level']) ?><?php echo $row['title'];?></option>
                <?php
            });
            ?>

        </select>

    </div>
    <div class="col-md-6">
        <label for="data_show_position" class="form-label">显示位置</label>
        <select name="zap_data[show_position]" class="form-select form-select-sm ">
            <?php foreach (AdminMenu::getPositions() as $id => $title): ?>
                <option value="<?php echo $id;?>"><?php echo $title;?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-12">
        <label for="data_icon" class="form-label">图标</label>
        <input type="text" class="form-control" name="zap_data[icon]" id="data_icon" value="fa fa-circle-notch">
    </div>
    <div class="col-md-12">
        <label for="data_link_to" class="form-label">链接地址</label>
        <input type="text" class="form-control" name="zap_data[link_to]" id="data_link_to" value="">
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
        <label for="data_link_type" class="form-label">链接类型</label>
        <select name="zap_data[link_type]" class="form-select form-select-sm ">
            <option value="action">Action</option>
            <option value="route">Route</option>
            <option value="link">自定义链接</option>
        </select>
    </div>
    <div class="col-md-12">
        <label for="data_active_rule" class="form-label">选中规则</label>
        <input type="text" class="form-control" name="zap_data[active_rule]" id="data_active_rule" value="">
    </div>

</form>
<script>
    var CATALOG_PID = <?php echo isset($parent['id']) ? $parent['id'] : 0;?>;

</script>