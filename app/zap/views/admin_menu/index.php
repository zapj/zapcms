<?php
$this->layout('layouts/common');

?>


<main class="container">


    <div class="my-3 p-3 bg-body rounded shadow-sm">
        <h6 class="border-bottom pb-2 mb-0">系统菜单设置</h6>
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">菜单名称</th>
                <th scope="col">排序</th>

            </tr>
            </thead>
            <tbody class="table-group-divider">
            <?php
            $menu->forEachAll(function($admin_menu){
                ?>
                <tr>
                    <td><i class="<?php echo $admin_menu['icon'];?>"></i> <?php echo $admin_menu['name'];?> </td>
                    <td><?php echo $admin_menu['sort_order'];?> </td>


                </tr>

            <?php
            });
            ?>


            </tbody>
        </table>

    </div>


</main>
