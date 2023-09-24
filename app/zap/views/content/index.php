<?php

use zap\facades\Url;
use zap\Catalog;
use zap\NodeType;


$this->layout('layouts/common');

?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 zap-top-bar">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('Content') ?>">内容管理</a></li>

            </ol>
        </nav>
        <div class=" text-end" >

        </div>
    </div>

</nav>

<main class="container-fluid zap-main">

    <div class="card shadow ">

        <div class="card-body">
            <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col">栏目名称</th>
                <th scope="col">内容模型</th>
                <th scope="col">排序</th>

            </tr>
            </thead>
            <tbody class="table-group-divider">
            <?php

            Catalog::instance()->forEachAll(function($catalog){
                $nodeType = NodeType::getNodeType($catalog['node_type']);
                $paddingLeft = ($catalog['level'] - 1) + ($catalog['level'] - 1) * 0.5;
                ?>
                <tr>
                    <td>
                        <i class="<?php echo $catalog['icon'];?>"></i>
                      <a href="<?php echo Url::action("{$nodeType['owner']}@{$nodeType['node_type']}",['catalog_id'=>$catalog['id']]); ?>">
                          <span style="padding-left: <?php echo $paddingLeft;?>rem!important;"><?php echo $catalog['title'];?></span>
                      </a>
                    </td>
                    <td><?php echo NodeType::getNodeTypeName($catalog['node_type']);?> </td>
                    <td><?php echo $catalog['sort_order'];?> </td>

                </tr>

            <?php
            });
            ?>


            </tbody>
        </table>
        </div>
    </div>


</main>
