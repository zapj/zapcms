<?php
use zap\facades\Url;

IS_AJAX !== true && $this->extend('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 ">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('User') ?>">用户管理</a></li>
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


    <div class="my-3 bg-body rounded shadow-sm">

        <script>

            function checkAll(el) {
                $('.zap_catalog').prop('checked', $(el).prop('checked'));
            }
        </script>
        <form action="post" id="reqForm">

            <div class="table-responsive">
                <table class="table table-hover text-nowrap">
                    <thead>
                    <tr class="table-secondary">
                        <th scope="col" style="width: 50px">
                            <label>
                                <input class="form-check-input" type="checkbox" onclick="Zap.CheckBox_CheckAll(this,'.users_list')"/>
                            </label>
                        </th>
                        <th scope="col">ID</th>
                        <th scope="col">用户名</th>
                        <th scope="col">姓名</th>
                        <th scope="col">邮箱</th>
                        <th scope="col">手机</th>
                        <th scope="col">状态</th>

                        <th scope="col">操作</th>


                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($users as $user){
                        ?>
                        <tr>
                            <td>
                                <?php if($user['id'] !=1){  ?>
                                <input name="admin[<?php echo $user['id']; ?>][id]"
                                       value="<?php echo $user['id']; ?>"
                                       class="form-check-input users_list" type="checkbox"/>
                                <?php } ?>
                            </td>

                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo $user['username']; ?></td>
                            <td><?php echo $user['full_name']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['phone_number']; ?></td>
                            <td>
                                <?php if($user['status'] == 'activated'){ ?>
                                <span class="badge rounded-pill text-bg-success"><i class="fa fa-check"></i></span>
                                <?php } else { ?>
                                    <span class="badge rounded-pill text-bg-secondary"><i class="fa fa-xmark"></i></span>
                                <?php } ?>
                            </td>

                            <td>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOrEdit(<?php echo $user['id']; ?>)">设置</button>

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

    function save(){
        $.ajax({
            url:'<?php echo Url::action("User@save");?>',
            method:'post',
            data:$('#reqForm').serialize(),
            success:function (data){
                ZapToast.alert(data.msg,{
                    bgColor:data.code===0?bgSuccess:bgDanger,
                    position:Toast_Pos_Center
                });
                Zap.reload();
            }
        })
    }

    function remove(){
        const checkedList = $('.users_list:checked').serialize();
        if(checkedList.length === 0){
            ZapToast.alert('请选选择需要删除的栏目',{bgColor:bgWarning,position:Toast_Pos_Center});
            return;
        }
        $.ajax({
            url:'<?php echo Url::action("User@remove");?>',
            method:'post',
            data:checkedList,
            success:function (data){
                ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger,position:Toast_Pos_Center});
                Zap.reload();
            }
        })
    }

    function addOrEdit(id){
        m = ZapModal.create({
            id:'addOrEditUser',
            title: (id ? '修改' : '添加') + '系统管理员',
            content:ZapModal.loadding(),
            backdrop:false,
            url:'<?php echo Url::action("User@form");?>?id='+id,
            buttons:[{close:true,title:"关闭"},{title:"保存",class:'btn-success'}],
            btn2:function (){
                $.ajax({
                    url:'<?php echo Url::action("User@saveUser");?>',
                    method:'post',
                    data:$('#addOrEditUser form').serialize(),
                    success:function (data){
                        ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger,position:Toast_Pos_Center});
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
