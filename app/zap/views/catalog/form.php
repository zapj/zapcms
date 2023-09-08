<?php
use zap\Catalog;
use zap\ContentType;
?>
<form>
<table class="table text-nowrap">
    <thead>
    <tr class="table-success">

        <th scope="col" style="width: 50px">排序</th>
        <th scope="col">栏目名称</th>
        <th scope="col">内容模型</th>
        <th scope="col">上级栏目</th>
        <th scope="col">显示位置</th>
    </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <input name="catalog[0][sort_order]" value="0" class="form-control form-control-sm" size="1" />
            </td>
            <td>
                <div >
                    <input name="catalog[0][title]" value="" class="form-control form-control-sm w-auto"/>
                </div>
            </td>

            <td>
                    <select name="catalog[0][content_type]" class="form-select form-select-sm w-auto">
                        <?php foreach (ContentType::getContentTypes() as $row): ?>
                        <option value="<?php echo $row['id'];?>"><?php echo $row['name'];?></option>
                        <?php endforeach; ?>
                    </select>
            </td>
            <td>
            <select name="catalog[0][pid]" class="form-select form-select-sm w-auto">
                <option value="0"> - 无 -</option>
                <?php
                Catalog::instance()->forEachAll(function($row){
                    ?>
                    <option value="<?php echo $row['id'];?>"><?php echo  str_repeat("&nbsp;&nbsp;",$row['level']) ?><?php echo $row['title'];?></option>
                <?php
                });
                ?>


            </select>
            </td>
            <td>
                    <select name="catalog[0][show_position]" class="form-select form-select-sm w-auto">
                        <?php foreach (Catalog::getPositions() as $id => $title): ?>
                            <option value="<?php echo $id;?>"><?php echo $title;?></option>
                        <?php endforeach; ?>
                    </select>
            </td>

        </tr>
    </tbody>

</table>
</form>
<script>
    var CATALOG_PID = <?php echo isset($parent['id']) ? $parent['id'] : 0;?>;

</script>