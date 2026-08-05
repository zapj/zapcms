<?php

use zap\facades\Url;

IS_AJAX !== true && $this->layout('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3">
    <div class="container-fluid">
        <div class="d-flex align-items-center w-100 justify-content-between">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                 aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo Url::action('Catalog') ?>">栏目管理</a></li>
                </ol>
            </nav>
            <div>
                <button type="button" class="btn btn-sm btn-success" onclick="addOrEdit(0)">
                    <i class="fa fa-plus me-1"></i><span class="d-none d-sm-inline">添加</span>
                </button>
            </div>
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
                <table class="table table-hover text-nowrap mb-0">
                    <thead>
                        <tr class="table-light">
                            <th scope="col" style="width: 40px" class="text-center align-middle">
                                <input class="form-check-input" type="checkbox" onclick="checkAll(this)"/>
                            </th>
                            <th scope="col" style="width: 60px">排序</th>
                            <th scope="col" class="w-100">栏目名称</th>
                            <th scope="col" class="d-none d-md-table-cell">SEO</th>
                            <th scope="col" class="d-none d-lg-table-cell">模型</th>
                            <th scope="col" style="width: 70px">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $menu->forEachAll(function ($admin_menu) {
                        $level = $admin_menu['level'];
                        $paddingLeft = $level * 1.5;
                        ?>
                        <tr class="catalog-row" data-level="<?php echo $level > 0 ? $level : ''; ?>">
                            <td class="text-center align-middle">
                                <input name="catalog[<?php echo $admin_menu['id']; ?>][id]"
                                       value="<?php echo $admin_menu['id']; ?>"
                                       class="form-check-input zap_catalog" type="checkbox"/>
                            </td>
                            <td class="align-middle">
                                <input name="catalog[<?php echo $admin_menu['id']; ?>][sort_order]"
                                       value="<?php echo $admin_menu['sort_order']; ?>"
                                       class="form-control form-control-sm sort-input" size="1" 
                                       data-bs-toggle="tooltip" data-bs-placement="right"
                                       data-bs-title="数值越小越靠前" />
                            </td>
                            <td class="align-middle">
                                <div class="catalog-title" style="padding-left:<?php echo $paddingLeft; ?>rem;">
                                    <i class="<?php echo $admin_menu['icon']; ?> menu-icon"></i>
                                    <input name="catalog[<?php echo $admin_menu['id']; ?>][title]"
                                           value="<?php echo $admin_menu['title']; ?>"
                                           class="d-inline form-control form-control-sm title-input"/>
                                    <small class="menu-id d-none d-sm-inline">#<?php echo $admin_menu['id'];?></small>
                                    <?php if($admin_menu['node_type']=='link-url'): ?>
                                    <i class="fa fa-external-link-alt text-muted" title="快捷链接"></i>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="align-middle d-none d-md-table-cell">
                                <small class="text-muted seo-cell"><?php echo $admin_menu['slug'] === '--zap-link-url' ? $admin_menu['link_to'] : $admin_menu['slug']; ?></small>
                            </td>
                            <td class="align-middle d-none d-lg-table-cell">
                                <span class="badge bg-light text-dark"><?php echo \zap\cms\NodeType::getTitle($admin_menu['node_type']); ?></span>
                            </td>
                            <td class="align-middle">
                                <button type="button" class="btn btn-outline-success action-btn" onclick="addOrEdit(<?php echo $admin_menu['id']; ?>)">
                                    <i class="fa fa-cog"></i>
                                </button>
                            </td>
                        </tr>
                        <?php
                    });
                    ?>
                    </tbody>
                </table>
            </div>
        </form>
        <div class="p-2 d-flex gap-2 flex-wrap">
            <button type="button" class="btn btn-success btn-sm" onclick="save()">
                <i class="fa fa-save me-1"></i>保存
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="remove()">
                <i class="fa fa-trash me-1"></i>删除
            </button>
        </div>
    </div>
</main>

<style>
/* 列表优化样式 */
.table {
    font-size: 0.875rem;
}

.table thead th {
    font-weight: 600;
    font-size: 0.8rem;
    padding: 0.75rem 0.5rem;
    white-space: nowrap;
    vertical-align: middle;
}

.table tbody td {
    padding: 0.625rem 0.5rem;
    vertical-align: middle;
}

.table tbody tr {
    transition: background-color 0.15s ease;
}

.table tbody tr:hover {
    background-color: rgba(16, 185, 129, 0.04);
}

/* 栏目名称单元格 */
.catalog-title {
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    gap: 0.5rem;
}

.catalog-title .title-input {
    max-width: 140px;
    flex-shrink: 0;
}

.catalog-title .menu-icon {
    color: #10b981;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}

.catalog-title .menu-id {
    color: #9ca3af;
    font-size: 0.75rem;
    flex-shrink: 0;
}

/* 嵌套层级指示线 - 只在栏目名称列显示 */
.catalog-row[data-level="1"] { --level-indent: 1.5rem; }
.catalog-row[data-level="2"] { --level-indent: 3rem; }
.catalog-row[data-level="3"] { --level-indent: 4.5rem; }

.catalog-row[data-level] td:nth-child(3) {
    position: relative;
}

.catalog-row[data-level] td:nth-child(3)::before {
    content: '';
    position: absolute;
    left: calc(var(--level-indent, 0) - 0.75rem);
    top: 50%;
    width: 8px;
    height: 8px;
    border-left: 2px solid #d1d5db;
    border-bottom: 2px solid #d1d5db;
    transform: translateY(-50%);
}

/* 排序输入框 */
.sort-input {
    width: 50px !important;
    text-align: center;
    padding: 0.25rem 0.25rem !important;
}

/* SEO单元格 */
.seo-cell {
    max-width: 120px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* 操作按钮 */
.action-btn {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.75rem;
}

/* 移动端适配 */
@media (max-width: 767px) {
    .table {
        font-size: 0.8rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem 0.35rem;
    }
    
    .sort-input {
        width: 40px !important;
        font-size: 0.7rem;
    }
    
    .catalog-title .title-input {
        max-width: 100px;
        font-size: 0.8rem;
    }
    
    .catalog-title .menu-icon {
        width: 16px;
        font-size: 0.9rem;
    }
    
    .seo-cell {
        display: none;
    }
    
    .action-btn {
        padding: 0.2rem 0.35rem !important;
    }
    
    .btn-success.btn-sm,
    .btn-outline-danger.btn-sm {
        font-size: 0.75rem;
        padding: 0.3rem 0.6rem;
    }
    
    .p-2 {
        padding: 0.75rem !important;
    }
    
    .gap-2 {
        gap: 0.5rem !important;
    }
}
</style>

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
