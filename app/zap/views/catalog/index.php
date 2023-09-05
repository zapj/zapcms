<?php
use zap\facades\Url;

$this->layout('layouts/common');
?>


<main class="container">


    <div class="my-3 bg-body rounded shadow">

        <script>
            function checkAll(el) {
                $('.zap_catalog').prop('checked', $(el).prop('checked'));
            }
        </script>
        <form action="post" id="reqForm">
        <table class="table table-striped table-hover">
            <thead>
                <tr class="table-success">
                    <th scope="col" style="width: 50px">
                        <label>
                            <input class="form-check-input" type="checkbox" onclick="checkAll(this)"/>
                        </label>
                    </th>
                    <th scope="col" style="width: 50px">排序</th>
                    <th scope="col">栏目名称</th>


                </tr>
            </thead>
            <tbody>
            <?php
            $menu->forEachAll(function ($admin_menu) {
                ?>
                <tr>
                    <td>
                        <input name="catalog[<?php echo $admin_menu['id']; ?>][id]"
                               value="<?php echo $admin_menu['id']; ?>"
                               class="form-check-input zap_catalog" type="checkbox"/>
                    </td>
                    <td>
                        <input name="catalog[<?php echo $admin_menu['id']; ?>][sort_order]"
                               value="<?php echo $admin_menu['sort_order']; ?>"
                               class="form-control form-control-sm"/>
                    </td>
                    <td><i class="<?php echo $admin_menu['icon']; ?>"></i> <?php echo $admin_menu['name']; ?> </td>


                </tr>

                <?php
            });
            ?>


            </tbody>


        </table>
        </form>
        <div class="pb-2 ps-2 pe-3">
            <button type="button" class="btn btn-success btn-sm" onclick="save()">保存</button>
            <button type="button" class="btn btn-secondary btn-sm">删除</button>
        </div>

    </div>


</main>
<script>
    function save(){

        $.ajax({
            url:'<?php echo Url::action("Catalog@save");?>',
            method:'post',
            data:$('#reqForm').serialize(),
            success:function (data){
                console.log(data);
            }
        })
    }


</script>
