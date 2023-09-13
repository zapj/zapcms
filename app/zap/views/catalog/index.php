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
        <div class="pt-2 pb-2 ps-1">
            <button type="button" class="btn btn-success" onclick="add(0)">添加</button>
        </div>
            <div class="table-responsive">
            <table class="table table-hover text-nowrap">
            <thead>
                <tr class="table-success">
                    <th scope="col" style="width: 50px">
                        <label>
                            <input class="form-check-input" type="checkbox" onclick="checkAll(this)"/>
                        </label>
                    </th>
                    <th scope="col" style="width: 50px">排序</th>
                    <th scope="col" class="w-100">栏目名称</th>
                    <th scope="col">显示位置</th>
                    <th scope="col">内容模型</th>
                    <th scope="col">SEO 名称</th>
                    <th scope="col">操作</th>


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
                                   class="form-control form-control-sm" size="1" data-bs-toggle="tooltip" data-bs-placement="right"
                                   data-bs-title="数值越小越靠前" />

                    </td>
                    <td>
                        <div style="padding-left:<?php echo $admin_menu['level']+($admin_menu['level']*0.5); ?>rem!important;">
                            <i class="<?php echo $admin_menu['icon']; ?>"></i>
                            <input name="catalog[<?php echo $admin_menu['id']; ?>][title]"
                                   value="<?php echo $admin_menu['title']; ?>"
                                   class="d-inline form-control form-control-sm w-auto"/>
                        </div>
                    </td>
                    <td>
                        <select name="catalog[<?php echo $admin_menu['id']; ?>][show_position]" class="form-select form-select-sm w-auto">
                            <?php foreach (\zap\Catalog::getPositions() as $id => $title): ?>
                                <option value="<?php echo $id;?>" <?php echo $admin_menu['show_position']==$id?'selected':''; ?> ><?php echo $title;?></option>
                            <?php endforeach; ?>
                        </select>

                        </td>
                    <td><?php echo \zap\NodeType::getNodeTypeName($admin_menu['module_name']); ?></td>
                    <td><?php echo $admin_menu['seo_url']; ?></td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm">设置</button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">更多</button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="javascript:void(0)" onclick="add(<?php echo $admin_menu['id'];?>)">添加子类</a></li>

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
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    })

    function save(){
        $.ajax({
            url:'<?php echo Url::action("Catalog@save");?>',
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
            ZapToast.alert('请选选择需要删除的栏目',{bgColor:bgWarning,position:Toast_Pos_Center});
            return;
        }
        $.ajax({
            url:'<?php echo Url::action("Catalog@remove");?>',
            method:'post',
            data:checkedCatalog,
            success:function (data){
                ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger,position:Toast_Pos_Center});
            }
        })
    }

    function add(pid){
        m = ZapModal.create({
            id:'addCatalog',
            title:'添加栏目',
            content:ZapModal.loadding(),
            backdrop:false,
            url:'<?php echo Url::action("Catalog@form");?>?modalId=addCatalog&pid='+pid,
            buttons:[{close:true,title:"关闭"},{title:"保存",class:'btn-success',callback:function(){alert(123);}}],
            btn2:function (){
                $.ajax({
                    url:'<?php echo Url::action("Catalog@saveCatalog");?>',
                    method:'post',
                    data:$('#addCatalog form').serialize(),
                    success:function (data){
                        ZapToast.alert(data.msg,{bgColor:data.code===0?bgSuccess:bgDanger,position:Toast_Pos_Center,delay:2000,callback:function(){
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
