<div class="col-md-3">
    <div class="card shadow d-none d-lg-block d-md-none">

        <div class="card-body p-0">
            <table class="table table-hover">

                <tbody >
                <?php

                use zap\Catalog;
                use zap\facades\Url;
                use zap\NodeType;

                Catalog::instance()->forEachAll(function($catalog) use ($catalogId){
                    $nodeType = NodeType::getNodeType($catalog['node_type']);
                    $paddingLeft = ($catalog['level'] - 1) + ($catalog['level'] - 1) * 0.5;
                    ?>
                    <tr class="<?php echo $catalogId == $catalog['id'] ? ' table-success':''; ?>">
                        <td>
                            <i class="<?php echo $catalog['icon'];?>"></i>
                            <a href="<?php echo Url::action("Node@{$nodeType['name']}",['catalog_id'=>$catalog['id']]); ?>">
                                <span style="padding-left: <?php echo $paddingLeft;?>rem!important;"><?php echo $catalog['title'];?></span>
                            </a>
                        </td>


                    </tr>

                    <?php
                });
                ?>


                </tbody>
            </table>
        </div>
    </div>
</div>