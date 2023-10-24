<?php
use zap\Catalog;
use zap\NodeType;
?>
<form>
    <input type="hidden" name="catalog_id" value="<?php echo $catalog['id'] ?? 0; ?>" />
    <div class="mb-3">
        <label for="catalog_title" class="form-label">栏目名称</label>
        <input type="text" class="form-control" id="catalog_title" name="catalog[title]" value="<?php echo $catalog['title'];?>" placeholder="">
    </div>
    <div class="mb-3">
        <label for="catalog_seo_name" class="form-label">SEO 名称</label>
        <input type="text" class="form-control" id="catalog_seo_name" name="catalog[seo_name]" value="<?php echo $catalog['seo_name'];?>" placeholder="">
    </div>
    <div class="mb-3">
        <label for="catalog_node_type" class="form-label">内容模型</label>
        <select class="form-select" id="catalog_node_type" name="catalog[node_type]" >
            <?php foreach (NodeType::getNodeTypes() as $key => $row):
                ?>
                <option value="<?php echo $row['type_name'];?>"  <?php echo $row['type_name'] == $catalog['node_type'] ?'selected':null;  ?> ><?php echo $row['title'];?></option>
            <?php endforeach; ?>
        </select>

    </div>
    <div class="mb-3">
        <label for="catalog_pid" class="form-label">上级栏目</label>
        <select class="form-select" id="catalog_pid" name="catalog[pid]" >
            <option value="0"> - 无 -</option>
            <?php
            Catalog::instance()->forEachAll(function($row) use ($catalog){
                ?>
                <option value="<?php echo $row['id'];?>" <?php echo $catalog['pid'] == $row['id'] ? 'selected':''; ?>
                    <?php echo $catalog['path'] && \zap\util\Str::startsWith($row['path'],$catalog['path']) ? 'disabled':null;  ?>
                >
                    <?php echo  str_repeat("&nbsp;&nbsp;",$row['level']) ?><?php echo $row['title'];?>
                </option>
                <?php
            });
            ?>
        </select>

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
        <label for="catalog_sort_order" class="form-label">排序</label>
        <input type="text" class="form-control" id="catalog_sort_order" name="catalog[sort_order]" placeholder="排序" value="<?php echo $catalog['sort_order'] ?? 0;?>">
    </div>

</form>
<script>
    var CATALOG_PID = <?php echo isset($parent['id']) ? $parent['id'] : 0;?>;

</script>