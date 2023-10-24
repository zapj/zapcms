<?php

use zap\BreadCrumb;
use zap\Catalog;
use zap\facades\Url;
use zap\NodeType;

$this->layout('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 ">
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

            <div class="card-header"><?php echo $title; ?></div>

            <div class="table-responsive card-body ps-0 pe-0">
                <table class="table text-nowrap table-hover">
                    <thead>
                    <tr>
                        <?php echo $catalogId ?'':'<th scope="col">类型</th>'  ?>
                        <th scope="col" class="w-50">标题</th>
                        <th scope="col">访问量</th>
                        <th scope="col">发布日期</th>
                        <th scope="col">状态</th>
                        <th scope="col" >操作</th>
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

                                echo "<th scope=\"col\">{$type}</th>";
                                }
                                ?>
                            <td>
                                <a href="<?php echo url_action("Node@{$class}/edit/{$row['id']}",$_GET);?>" class="link-underline link-underline-opacity-0">
                                    <?php echo $row['title'];?>
                                </a><small class="text-black-50"><?php echo 'ID:',$row['id'];?></small>
                            </td>

                            <td><?php echo $row['hits']; ?></td>
                            <td><?php echo date(Z_DATE_TIME,$row['pub_time']); ?></td>
                            <td><?php echo \zap\Node::getStatusTitle($row['status']); ?></td>
                            <td>
                                <a href="<?php echo url_action("Node@{$class}/edit/{$row['id']}",$_GET);?>" class="btn btn-sm btn-outline-success"><i class="fa fa-edit"></i> 编辑</a>
                                <a href="javascript:void(0);" onclick="remove(<?php echo $row['id'];?>,'<?php echo $row['title'];?>');" class="btn btn-sm btn-outline-danger"><i class="fa fa-remove"></i> 删除</a>

                            </td>
                        </tr>
                    <?php endforeach; ?>

                    </tbody>
                </table>
                <div>
                    <?php echo $page->render(7,'pagination  justify-content-center','page-item' ,'page-link'); ?>
                </div>
            </div>
        </div>

    </div>
</div>


</main>
<script >

    function remove(id,title){
        layer.confirm("确认删除 【"+title+"】 吗？", {icon: 3, title:'提示'}, function(index){
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
            }).always(function (){
                layer.close(index);
            })

        });
    }

</script>
