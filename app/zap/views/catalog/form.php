<?php

use zap\cms\Catalog;
use zap\cms\NodeType;

?>
<form>
    <input type="hidden" name="catalog_id" value="<?php echo $catalog['id'] ?? 0; ?>" />
    <div class="row">
        <div class="col-md-6">
            <label for="catalog_pid" class="form-label">上级栏目</label>
            <select class="form-select form-select-sm" id="catalog_pid" name="catalog[pid]" >
                <option value="0"> - 无 -</option>
                <?php
                Catalog::instance()->forEachAll(function($row) use ($catalog){
                    ?>
                    <option value="<?php echo $row['id'];?>" <?php echo $catalog['pid'] == $row['id'] ? 'selected':''; ?>
                        <?php echo $catalog['path'] && \zap\util\Str::startsWith($row['path'],$catalog['path']) ? 'disabled':null;  ?>
                    >
                        <?php echo  str_repeat("&nbsp;&nbsp;",$row['level'] ) ?><?php echo $row['title'];?>
                    </option>
                    <?php
                });
                ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="catalog_node_type" class="form-label">内容模型</label>
            <select class="form-select form-select-sm" id="catalog_node_type" name="catalog[node_type]" onchange="chNodeType(this)" >
                <?php foreach (NodeType::getNodeTypes() as $key => $row):
                    ?>
                    <option value="<?php echo $row['type_name'];?>"  <?php echo $row['type_name'] == $catalog['node_type'] ?'selected':null;  ?> ><?php echo $row['title'];?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="catalog_title" class="form-label">栏目名称</label>
            <input type="text" class="form-control form-control-sm" id="catalog_title" name="catalog[title]" value="<?php echo $catalog['title'];?>" placeholder="">
        </div>
        <div class="col-md-6 <?php if_echo('link-url' === $catalog['node_type'],'d-none'); ?>" id="node_slug_id">
            <label for="catalog_slug" class="form-label">别名</label>
            <input type="text" class="form-control form-control-sm" id="catalog_slug" name="catalog[slug]" value="<?php echo $catalog['slug'];?>" placeholder="字母、中文、数字和-_">
        </div>
        <div class="col-md-6">
            <label for="catalog_sort_order" class="form-label">排序</label>
            <input type="text" class="form-control form-control-sm" id="catalog_sort_order" name="catalog[sort_order]" placeholder="排序" value="<?php echo $catalog['sort_order'] ?? 0;?>">
        </div>
    </div>



    <div class="mb-3">
        </div>
    <div class="mb-3">
        <label for="catalog_show_position" class="form-label">显示位置</label><br/>
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
    <div class="mb-3">
        </div>


    <div id="extras_panel" class="<?php if_echo('link-url' !== $catalog['node_type'],'d-none'); ?>">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6"></div>
        </div>
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6"></div>
        </div>
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6"></div>
        </div>
        <div class="row mb-3">
            <div class="col-6">
                <label for="catalog_link_type" class="form-label">链接类型</label>
                <select class="form-select form-select-sm" id="catalog_link_type" name="catalog[link_type]">
                    <option value="node" <?php if($catalog['link_type']==='node'){echo 'selected';} ?> >内容</option>
                    <option value="custom_link" <?php if($catalog['link_type']==='custom_link'){echo 'selected';} ?>>自定义链接</option>
                </select>
            </div>
            <div class="col-6">
                <label for="catalog_link_to" class="form-label">链接地址</label>
                <input type="text" class="col-2 form-control" id="catalog_link_to" name="catalog[link_to]" placeholder="链接地址" value="<?php echo $catalog['link_to'] ?? '';?>">
            </div>
            <div class="col-6">
                <label for="catalog_link_target" class="form-label">打开方式</label>
                <select class="form-select form-select-sm" id="catalog_link_target" name="catalog[link_target]">
                    <option value="_self" <?php if_echo($catalog['link_target'] === '_self','selected'); ?>>当前页面</option>
                    <option value="_blank" <?php if_echo($catalog['link_target'] === '_blank','selected'); ?>>新页面</option>
                </select>

            </div>
            <div class="col-6">
                <label for="catalog_link_object" class="form-label">Link Object</label>
                <input type="text" class="col-2 form-control" id="catalog_link_object" name="catalog[link_object]"  value="<?php echo $catalog['link_object'] ?? 0;?>">

            </div>


        </div>
    </div>


</form>
<script>
    function chNodeType(el) {
        if(el.value === 'link-url'){
            $('#extras_panel').removeClass('d-none');
            $('#node_slug_id').addClass('d-none');
        }else{
            $('#extras_panel').addClass('d-none');
            $('#node_slug_id').removeClass('d-none');
        }
    }

</script>