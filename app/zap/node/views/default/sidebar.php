<div class="col-md-3 d-none d-md-block d-lg-block mb-3" id="nodeleftsidebar">

    <div class="card shadow ">
        <div class="card-header"><i class="fa fa-square-poll-horizontal"></i> 栏目</div>
        <div class="card-body p-0">
            <table class="table table-hover">

                <tbody >
                <?php
                use zap\Catalog;
                use zap\facades\Url;
                use zap\NodeType;
                if($catalogId===0){
                    $catalogId = $node->id;
                }
                Catalog::instance()->forEachAll(function($catalog) use ($catalogId){
                    $nodeType = NodeType::getNodeType($catalog['node_type']);
                    ?>
                    <tr class="<?php echo $catalogId == $catalog['id'] ? ' table-success':''; ?>">
                        <td>
                            <i class="<?php echo $catalog['icon'];?>"></i>
                            <a href="<?php echo Url::action("Node@{$nodeType['type_name']}",['cid'=>$catalog['id']]); ?>">
                                <span style="padding-left: <?php echo $catalog['level'];?>rem!important;"><?php echo $catalog['title'];?></span>
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