<div class="col-md-3 d-none d-md-block d-lg-block mb-3" id="nodeleftsidebar">

    <div class="card shadow-sm ">
        <div class="card-header"><i class="fa fa-square-poll-horizontal"></i> 栏目</div>
        <div class="card-body p-0">
            <table class="table table-hover">

                <tbody >
                <?php

                use zap\cms\Catalog;
                use zap\facades\Url;

                if($catalogId===0){
                    $catalogId = $node->id;
                }
                Catalog::instance()->forEachAll(function($catalog) use ($catalogId){
                    if($catalog['node_type'] === 'link-url'){
                        $catalog['node_type'] = $catalog['link_object'] ? $catalog['content'] :$catalog['node_type'];
                    }
                    ?>
                    <tr class="<?php echo $catalogId == $catalog['id'] ? ' table-success':''; ?>">
                        <td>
                            <i class="<?php echo $catalog['icon'];?>"></i>
                            <a href="<?php echo Url::action("Node@{$catalog['node_type']}",['cid'=>$catalog['link_object']?:$catalog['id']]); ?>">
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