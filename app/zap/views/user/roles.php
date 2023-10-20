<?php
use zap\facades\Url;

IS_AJAX !== true && $this->extend('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 ">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item "><a href="<?php echo Url::action('User') ?>">用户管理</a></li>
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('User@roles') ?>">角色管理</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
            <a href="<?php echo url_action('User@roles') ?>" class="btn btn-success btn-sm" ><i class="fa-solid fa-user-gear"></i> 角色管理</a>
            <a href="<?php echo url_action('User@permissions') ?>" class="btn btn-success btn-sm" >权限管理</a>
            <button type="button" class="btn btn-sm btn-success" onclick="addOrEdit(0)"><i class="fa fa-plus"></i> 添加</button>
        </div>
    </div>

</nav>
<main class="container zap-main">


    <div class="my-3 bg-body rounded shadow">

        <script>

            function checkAll(el) {
                $('.zap_catalog').prop('checked', $(el).prop('checked'));
            }
        </script>
        <form action="" method="post" id="reqForm">

            <div class="table-responsive">
                <table class="table table-hover text-nowrap">
                    <thead>
                    <tr class="table-secondary">
                        <th scope="col" style="width: 50px">
                            <label>
                                <input class="form-check-input" type="checkbox" onclick="checkAll(this)"/>
                            </label>
                        </th>
                        <th scope="col">ID</th>
                        <th scope="col">名称</th>
                        <th scope="col">修改时间</th>
                        <th scope="col">创建时间</th>


                        <th scope="col">操作</th>


                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($data as $item){
                        ?>
                        <tr>
                            <td>
                                <?php if($item['role_id'] !=1){  ?>
                                <input name="data[<?php echo $item['role_id']; ?>][role_id]"
                                       value="<?php echo $item['role_id']; ?>"
                                       class="form-check-input zap_catalog" type="checkbox"/>
                                <?php } ?>
                            </td>

                            <td><?php echo $item['role_id']; ?></td>
                            <td><?php echo $item['name']; ?></td>
                            <td><?php echo date(Z_DATE_TIME,$item['updated_at']); ?></td>
                            <td><?php echo date(Z_DATE_TIME,$item['created_at']); ?></td>

                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOrEdit(<?php echo $item['role_id']; ?>)">编辑</button>

                            </td>

                        </tr>

                        <?php
                    };
                    ?>


                    </tbody>


                </table>
                <div class="pb-2 ps-2 pe-3">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="remove()">删除</button>
                </div>
                <?php echo $pageHelper->render(7,'pagination  justify-content-center','page-item' ,'page-link'); ?>

            </div>
        </form>


    </div>


</main>
<script>
    $(function (){
        Zap.EnableToolTip();
    })

    function remove(){
        const checkedCatalog = $('.zap_catalog:checked').serialize();
        if(checkedCatalog.length === 0){
            ZapToast.alert('请选选择需要删除的角色',{bgColor:bgWarning});
            return;
        }
        $.ajax({
            url:'<?php echo Url::action("User@removeRole");?>',
            method:'post',
            data:checkedCatalog,
            success:function (data){
                ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger});
                Zap.reload();
            }
        })
    }

    function addOrEdit(id){
        m = ZapModal.create({
            id:'addRole',
            title: (id ? '修改' : '添加') + '角色',
            content:ZapModal.loadding(),
            backdrop:false,
            url:'<?php echo Url::action("User@formRole");?>?id='+id,
            buttons:[{close:true,title:"关闭"},{title:"保存",class:'btn-success'}],
            btn2:function (){
                $.ajax({
                    url:'<?php echo Url::action("User@saveRole");?>',
                    method:'post',
                    data:$('#addRole form').serialize(),
                    success:function (data){
                        ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger});
                        Zap.reload();
                    }
                }).always(function(){
                    m.hide();
                })
            }
        },true)
        m.show();
    }


</script>
