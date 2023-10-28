<?php
use zap\facades\Url;

IS_AJAX !== true && $this->layout('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 ">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('Catalog') ?>">栏目管理</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
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
                    <th scope="col" class="w-100">栏目名称</th>
                    <th scope="col">SEO 名称</th>
                    <th scope="col">内容模型</th>

                    <th scope="col">操作</th>


                </tr>
            </thead>
            <tbody>
            <?php
            $menu->forEachAll(function ($admin_menu) {
                $paddingLeft = $admin_menu['level'] ;
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
                                   class="form-control form-control-sm" size="1" data-bs-toggle="tooltip" data-bs-placement="right"
                                   data-bs-title="数值越小越靠前" />

                    </td>
                    <td>
                        <div style="padding-left:<?php echo $paddingLeft; ?>rem!important;">
                            <i class="<?php echo $admin_menu['icon']; ?>"></i>
                            <input name="catalog[<?php echo $admin_menu['id']; ?>][title]"
                                   value="<?php echo $admin_menu['title']; ?>"
                                   class="d-inline form-control form-control-sm w-auto"/>
                        </div>
                    </td>
                    <td><?php echo $admin_menu['slug']; ?></td>
                    <td><?php echo \zap\NodeType::getTitle($admin_menu['node_type']); ?></td>

                    <td>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="addOrEdit(<?php echo $admin_menu['id']; ?>)">设置</button>

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
            url:'<?php echo Url::action("Catalog@save");?>',
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
        const checkedCatalog = $('.zap_catalog:checked').serialize();
        if(checkedCatalog.length === 0){
            ZapToast.alert('请选选择需要删除的栏目',{bgColor:bgWarning,position:Toast_Pos_Center});
            return;
        }
        $.ajax({
            url:'<?php echo Url::action("Catalog@remove");?>',
            method:'post',
            data:checkedCatalog,
            success:function (data){
                ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger,position:Toast_Pos_Center});
                Zap.reload();
            }
        })
    }

    function addOrEdit(cid){
        m = ZapModal.create({
            id:'addCatalog',
            title: (cid ? '修改' : '添加') + '栏目',
            content:ZapModal.loadding(),
            backdrop:false,
            url:'<?php echo Url::action("Catalog@form");?>?modalId=addCatalog&pid=0&cid='+cid,
            buttons:[{close:true,title:"关闭"},{title:"保存",class:'btn-success'}],
            btn2:function (){
                $.ajax({
                    url:'<?php echo Url::action("Catalog@saveCatalog");?>',
                    method:'post',
                    data:$('#addCatalog form').serialize(),
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
