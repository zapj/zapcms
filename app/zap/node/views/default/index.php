<?php

use zap\cms\BreadCrumb;
use zap\cms\NodeType;

$this->layout('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 ">
    <div class="container-fluid">
        <?php BreadCrumb::instance()->display('<li class="d-block d-md-none d-lg-none"><i class="fa fa-bars me-1" onclick="$(\'#nodeleftsidebar\').toggleClass(\'d-none\');"></i> </li>') ; ?>

        <div class=" text-end" >
            <a class="btn btn-success btn-sm" href="<?php echo url_action("Node@{$_controller}/add",$_GET);?>">
                <i class="fa fa-add"></i> 添加</a>
        </div>
    </div>

</nav>


<main class="container-fluid zap-main">


<div class="row">
    <?php include('sidebar.php'); ?>
    <div class="col-md-9 col-lg-9 col-sm-12">
        <div class="card shadow-sm">

            <div class="card-header d-flex align-items-center justify-content-between">
                <span><?php echo $title; ?></span>
                <a class="btn btn-success btn-sm" href="<?php echo url_action("Node@{$_controller}/add",$_GET);?>">
                    <i class="fa fa-plus me-1"></i>添加
                </a>
            </div>

            <div class="table-responsive card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                    <tr class="table-light">
                        <?php echo $catalogId ?'':'<th scope="col" style="width: 100px;">类型</th>'  ?>
                        <th scope="col">标题</th>
                        <th scope="col" style="width: 80px;">访问量</th>
                        <th scope="col" style="width: 150px;">发布日期</th>
                        <th scope="col" style="width: 80px;">状态</th>
                        <th scope="col" style="width: 140px;">操作</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data as $row):
                        $class = $row['node_type'];
                        ?>

                        <tr>
                            <?php
                            if($catalogId==0) {
                                $type = NodeType::getTitle($row['node_type']);
                                echo "<td class=\"align-middle\"><span class=\"badge bg-success\">{$type}</span></td>";
                                }
                                ?>
                            <td class="align-middle">
                                <a href="<?php echo url_action("Node@{$class}/edit/{$row['id']}",$_GET);?>" class="text-decoration-none fw-medium">
                                    <?php echo $row['title'];?>
                                </a>
                                <small class="text-muted ms-1">#<?php echo $row['id'];?></small>
                            </td>

                            <td class="align-middle text-center"><small><?php echo $row['hits']; ?></small></td>
                            <td class="align-middle"><small><?php echo date('Y-m-d H:i',$row['pub_time']); ?></small></td>
                            <td class="align-middle">
                                <?php 
                                $statusClass = $row['status'] === 'published' ? 'bg-success' : 'bg-secondary';
                                $statusTitle = \zap\cms\models\Node::getStatusTitle($row['status']);
                                echo "<span class=\"badge {$statusClass} rounded-pill\">{$statusTitle}</span>";
                                ?>
                            </td>
                            <td class="align-middle">
                                <a href="<?php echo url_action("Node@{$class}/edit/{$row['id']}",$_GET);?>" class="btn btn-outline-success btn-sm action-btn">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="javascript:void(0);" onclick="remove(<?php echo $row['id'];?>,'<?php echo $row['title'];?>');" class="btn btn-outline-danger btn-sm action-btn">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
                <div class="p-2 border-top">
                    <?php echo $page->render(7,'pagination justify-content-center justify-content-sm-end','page-item' ,'page-link'); ?>
                </div>
            </div>
        </div>

    </div>
</div>


</main>
<style>
/* 内容列表样式 */
.table td:first-child,
.table th:first-child {
    padding-left: 1rem;
}

.table td:last-child,
.table th:last-child {
    padding-right: 1rem;
}

/* 标题单元格 */
.title-cell {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* 操作按钮 */
.action-btn {
    padding: 0.25rem 0.5rem !important;
    font-size: 0.75rem;
}

/* 移动端适配 */
@media (max-width: 767px) {
    .card-header {
        padding: 0.75rem 1rem;
        font-size: 0.9rem;
    }
    
    .table {
        font-size: 0.8rem;
    }
    
    .table th,
    .table td {
        padding: 0.5rem 0.35rem;
    }
    
    .table td:first-child,
    .table th:first-child {
        padding-left: 0.5rem;
    }
    
    .table td:last-child,
    .table th:last-child {
        padding-right: 0.5rem;
    }
    
    .action-btn {
        padding: 0.2rem 0.35rem !important;
        font-size: 0.7rem;
    }
    
    .pagination {
        font-size: 0.8rem;
    }
    
    .page-link {
        padding: 0.35rem 0.65rem;
    }
    
    /* 隐藏次要列 */
    .table-responsive {
        border: none;
    }
}
</style>

<script >

    function remove(id,title){
        const m = ZapModal.create({
            id: 'confirmDelete',
            title: '提示',
            content: "确认删除 【"+title+"】 吗？",
            backdrop: false,
            buttons: [
                {close: true, title: "取消", class: 'btn-outline-secondary'},
                {title: "确定", class: 'btn-danger'}
            ],
            btn2: function() {
                $.ajax({
                    url: '<?php echo url_action("Node@{$_controller}/remove");?>',
                    method: 'POST',
                    data: {id:id},
                    dataType: 'json',
                    success: function (data) {
                        if(data.code === 0){
                            location.reload();
                        }else{
                            ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
                        }
                    }
                }).always(function(){
                    m.hide();
                })
            }
        }, true);
        m.show();
    }

</script>
