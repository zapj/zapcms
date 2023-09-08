<?php

use zap\facades\Url;
use zap\Catalog;
use zap\ContentType;

$this->layout('layouts/common');
?>


<main class="container-fluid">

    <div class="row p-0">
        <div class="col pt-2 pb-0">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo Url::action('Content') ?>">内容管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page">编辑的模块</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="my-3 p-3 bg-body rounded shadow-sm">
        <h6 class="border-bottom pb-2 mb-0">内容管理</h6>
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col">栏目名称</th>
                <th scope="col">排序</th>

            </tr>
            </thead>
            <tbody class="table-group-divider">
            <?php

            Catalog::instance()->forEachAll(function($catalog){
                $contentType = ContentType::getContentType($catalog['content_type']);

                ?>
                <tr>
                    <td>
                        <i class="<?php echo $catalog['icon'];?>"></i>
                      <a href="<?php echo Url::action("Content@{$contentType['module_name']}"); ?>">
                          <span style="padding-left: <?php echo $catalog['level'];?>rem!important;"><?php echo $catalog['title'];?></span>
                      </a>
                    </td>
                    <td><?php echo $catalog['sort_order'];?> </td>


                </tr>

            <?php
            });
            ?>


            </tbody>
        </table>

    </div>


</main>
