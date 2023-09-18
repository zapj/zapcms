<?php
use zap\facades\Url;

$this->layout('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 ">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item "><a href="<?php echo Url::action('System@settings') ?>">设置</a></li>
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('AdminMenu') ?>">系统菜单管理</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
            <button type="button" class="btn btn-sm btn-success" onclick="addOrEdit(0)"><i class="fa fa-plus"></i> 添加</button>
        </div>
    </div>

</nav>

<main class="container mt-65px">


    <div class="card shadow">

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
                            <input class="form-check-input" type="checkbox" onclick="checkAll(this)"/>
                        </label>
                    </th>
                    <th scope="col" style="width: 50px">排序</th>
                    <th scope="col" class="w-100">菜单名称</th>
                    <th scope="col">链接</th>
                    <th scope="col">显示位置</th>

                    <th scope="col">操作</th>


                </tr>
            </thead>
            <tbody>
            <?php
            $menu->forEachAll(function ($admin_menu) {
                $paddingLeft = ($admin_menu['level'] - 1) + ($admin_menu['level'] - 1) * 0.5;
                ?>
                <tr>
                    <td>
                        <input name="admin_menu[<?php echo $admin_menu['id']; ?>][id]"
                               value="<?php echo $admin_menu['id']; ?>"
                               class="form-check-input zap_catalog" type="checkbox"/>
                    </td>
                    <td>

                            <input name="admin_menu[<?php echo $admin_menu['id']; ?>][sort_order]"
                                   value="<?php echo $admin_menu['sort_order']; ?>"
                                   class="form-control form-control-sm" size="1" data-bs-toggle="tooltip" data-bs-placement="right"
                                   data-bs-title="数值越小越靠前" />

                    </td>
                    <td>
                        <div style="padding-left:<?php echo $paddingLeft; ?>rem!important;">
                            <i class="<?php echo $admin_menu['icon']; ?>"></i>
                            <input name="admin_menu[<?php echo $admin_menu['id']; ?>][title]"
                                   value="<?php echo $admin_menu['title']; ?>"
                                   class="d-inline form-control form-control-sm w-auto"/>
                        </div>
                    </td>
                    <td><?php echo $admin_menu['link_to']; ?></td>
                    <td>
                        <select name="admin_menu[<?php echo $admin_menu['id']; ?>][show_position]" class="form-select form-select-sm w-auto">
                            <?php foreach (\zap\AdminMenu::getPositions() as $id => $title): ?>
                                <option value="<?php echo $id;?>" <?php echo $admin_menu['show_position']==$id?'selected':''; ?> ><?php echo $title;?></option>
                            <?php endforeach; ?>
                        </select>

                        </td>


                    <td>
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="addOrEdit(<?php echo $admin_menu['pid'],',',$admin_menu['id'];?>)">设置</button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">更多</button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="addOrEdit(<?php echo $admin_menu['id'];?>)">添加子类</a></li>

<!--                                <li><hr class="dropdown-divider"></li>-->
                            </ul>
                        </div>
                    </td>

                </tr>

                <?php
            });
            ?>


            </tbody>


        </table>
            </div>
        </form>
        <div class="pb-2 ps-2 pe-3">
            <button type="button" class="btn btn-success btn-sm" onclick="save()">保存</button>
            <button type="button" class="btn btn-secondary btn-sm" onclick="remove()">删除</button>
        </div>

    </div>


</main>
<script>
    $(function (){
        Zap.EnableToolTip();
    })

    function save(){
        $.ajax({
            url:'<?php echo Url::action("AdminMenu@save");?>',
            method:'post',
            data:$('#reqForm').serialize(),
            success:function (data){
                ZapToast.alert(data.msg,{
                    bgColor:data.code===0?bgSuccess:bgDanger,
                    position:Toast_Pos_Center,
                    delay: 2000,
                    callback:function(){ location.reload();}
                });
            }
        })
    }

    function remove(){
        const checkedCatalog = $('.zap_catalog:checked').serialize();
        if(checkedCatalog.length === 0){
            ZapToast.alert('请选选择需要删除的菜单',{bgColor:bgWarning,position:Toast_Pos_Center});
            return;
        }
        $.ajax({
            url:'<?php echo Url::action("AdminMenu@remove");?>',
            method:'post',
            data:checkedCatalog,
            success:function (data){
                ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger,position:Toast_Pos_Center});
            }
        })
    }

    function addOrEdit(pid,menuId){
        var formUrl = '<?php echo Url::action("AdminMenu@form");?>?modalId=adminMenu&pid='+pid;
        if(menuId!==undefined){
            formUrl += '&id='+menuId;
        }
        const m = ZapModal.create({
            id:'adminMenu',
            title: menuId===undefined ? '添加栏目' : '修改栏目',
            content:ZapModal.loadding(),
            backdrop:false,
            url: formUrl,
            buttons:[{close:true,title:"关闭"},{title:"保存",class:'btn-success',callback:function(){alert(123);}}],
            btn2:function (){
                $.ajax({
                    url:'<?php echo Url::action("AdminMenu@saveAdminMenu");?>',
                    method:'post',
                    data:$('#adminMenu form').serialize(),
                    success:function (data){
                        ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger,position:Toast_Pos_Center,delay:1500,callback:function(){
                            location.reload();
                            }
                        });
                    }
                })
            }
        },true)
        m.show();
    }


</script>
