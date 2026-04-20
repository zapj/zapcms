<?php
use zap\facades\Url;

IS_AJAX !== true && $this->extend('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3">
    <div class="container-fluid">
        <div class="d-flex align-items-center w-100 justify-content-between">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                 aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo Url::action('User') ?>">用户管理</a></li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="<?php echo url_action('User@roles') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-user-gear me-1"></i><span class="d-none d-md-inline">角色</span>
                </a>
                <a href="<?php echo url_action('User@permissions') ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-shield-halved me-1"></i><span class="d-none d-md-inline">权限</span>
                </a>
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
                            <th scope="col" style="width: 40px" class="text-center">
                                <input class="form-check-input" type="checkbox" onclick="Zap.CheckBox_CheckAll(this,'.users_list')"/>
                            </th>
                            <th scope="col" style="width: 50px">ID</th>
                            <th scope="col">用户</th>
                            <th scope="col" class="d-none d-md-table-cell">姓名</th>
                            <th scope="col" class="d-none d-lg-table-cell">邮箱</th>
                            <th scope="col" class="d-none d-xl-table-cell">手机</th>
                            <th scope="col" style="width: 60px">状态</th>
                            <th scope="col" style="width: 60px">操作</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($users as $user){
                        ?>
                        <tr>
                            <td class="text-center align-middle">
                                <?php if($user['id'] != 1){ ?>
                                <input name="admin[<?php echo $user['id']; ?>][id]"
                                       value="<?php echo $user['id']; ?>"
                                       class="form-check-input users_list" type="checkbox"/>
                                <?php } ?>
                            </td>
                            <td class="align-middle"><span class="badge bg-light text-dark"><?php echo $user['id']; ?></span></td>
                            <td class="align-middle">
                                <div class="user-cell">
                                    <div class="avatar avatar-sm bg-success text-white">
                                        <?php echo mb_substr($user['username'], 0, 1); ?>
                                    </div>
                                    <span class="username"><?php echo $user['username']; ?></span>
                                </div>
                            </td>
                            <td class="align-middle d-none d-md-table-cell"><?php echo $user['full_name'] ?: '-'; ?></td>
                            <td class="align-middle d-none d-lg-table-cell"><small><?php echo $user['email'] ?: '-'; ?></small></td>
                            <td class="align-middle d-none d-xl-table-cell"><small><?php echo $user['phone_number'] ?: '-'; ?></small></td>
                            <td class="align-middle">
                                <?php if($user['status'] == 'activated'): ?>
                                <span class="badge bg-success rounded-pill"><i class="fa fa-check"></i></span>
                                <?php else: ?>
                                <span class="badge bg-secondary rounded-pill"><i class="fa fa-times"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <button type="button" class="btn btn-outline-success action-btn" onclick="addOrEdit(<?php echo $user['id']; ?>)">
                                    <i class="fa fa-cog"></i>
                                </button>
                            </td>
                        </tr>
                        <?php
                    };
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="p-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="remove()">
                    <i class="fa fa-trash me-1"></i>删除
                </button>
                <?php echo $pageHelper->render(7,'pagination justify-content-center justify-content-sm-end','page-item' ,'page-link'); ?>
            </div>
        </form>
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

/* 紧凑行 */
.table-compact .table th,
.table-compact .table td {
    padding: 0.5rem 0.5rem;
}

/* 用户头像与文字对齐 */
.user-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.user-cell .username {
    font-weight: 500;
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
    
    .avatar-sm {
        width: 24px;
        height: 24px;
        font-size: 0.7rem;
    }
    
    .user-cell {
        gap: 0.35rem;
    }
    
    .user-cell .username {
        max-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .badge {
        font-size: 0.6rem;
        padding: 0.2em 0.35em;
    }
    
    .action-btn {
        padding: 0.2rem 0.35rem !important;
    }
    
    .pagination {
        font-size: 0.8rem;
    }
    
    .page-link {
        padding: 0.35rem 0.65rem;
    }
    
    .breadcrumb {
        font-size: 0.8rem;
    }
}
</style>

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
            ZapToast.alert('请选选择需要删除的用户',{bgColor:bgWarning,position:Toast_Pos_Center});
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
