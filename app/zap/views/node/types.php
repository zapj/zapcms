<?php

use zap\facades\Url;

$this->layout('layouts/common');
?>

<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 ">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo Url::action('Content') ?>">内容管理</a></li>
                <li class="breadcrumb-item active" aria-current="page"><a
                        href="<?php echo Url::action('Node@types',$_GET) ?>">内容模型管理</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
            <a class="btn btn-success btn-sm" href="<?php echo url_action("Zap@{$_controller}/add",$_GET);?>">
                <i class="fa fa-add"></i> 添加</a>
        </div>
    </div>

</nav>


<main class="container-fluid zap-main">



    <div class="card shadow-sm">

        <div class="card-header"><?php echo $title; ?></div>

        <div class="table-responsive card-body">
            <table class="table text-nowrap table-hover">
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col" class="w-50">标题</th>
                    <th scope="col">点击量</th>
                    <th scope="col">发布日期</th>
                    <th scope="col">状态</th>
                    <th scope="col" >操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <th scope="row"><?php echo $row['id'];?></th>
                        <td>
                            <a href="<?php echo url_action("Node@{$_controller}/edit/{$row['id']}",$_GET);?>">
                                <?php echo $row['title'];?>
                            </a>
                        </td>

                        <td><?php echo $row['hits']; ?></td>
                        <td><?php echo date(Z_DATE_TIME,$row['pub_time']); ?></td>
                        <td><?php echo \zap\cms\models\Node::getStatusTitle($row['status']); ?></td>
                        <td>
                            <a href="<?php echo url_action("Node@{$_controller}/edit/{$row['id']}",$_GET);?>" class="btn btn-sm btn-success"><i class="fa fa-edit"></i> 编辑</a>
                            <a href="javascript:void(0);" onclick="remove(<?php echo $row['id'];?>,'<?php echo $row['title'];?>');" class="btn btn-sm btn-danger"><i class="fa fa-remove"></i> 删除</a>

                        </td>
                    </tr>
                <?php endforeach; ?>

                </tbody>
            </table>
            <div>
<!--                --><?php //echo $page->render(7,'pagination  justify-content-center','page-item' ,'page-link'); ?>
            </div>
        </div>
    </div>


</main>
<script >

    function remove(id,title){
        layer.confirm("确认删除 【"+title+"】 吗？", {icon: 3, title:'提示'}, function(index){
            $.ajax({
                url: '<?php echo url_action('Zap@{$_controller}/remove');?>',
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
