<?php use zap\BreadCrumb;

echo $this->extend('layout/default'); ?>
<div class="bread_area">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <?php echo BreadCrumb::instance()->display(); ?>
            </div>
        </div>
    </div>
</div>
<main class="site-main product-main">
    <div class="container">
        <div class="row">
            <aside class="sidebar col-sm-3">
                <div class="widget">
                    <h4><?php
                        $topCatalog = array_shift(pageState()->subCatalogList);
                        echo $topCatalog['title']; ?></h4>
                    <ul>
                        <?php foreach(pageState()->subCatalogList as $catalog){ ?>
                            <li <?php if(pageState()->nodeId == $catalog['id']){echo 'class="current"';} ?>
                            ><a href="<?php echo site_url("/{$catalog['slug']}"); ?>" title="<?php echo $catalog['title'];?>"><?php echo $catalog['title'];?></a></li>
                        <?php } ?>
                    </ul>
                </div>
            </aside>
            <section class="category-content col-sm-9">
<!--                <h2 class="category-title">产品列表</h2>-->
                <div class="row product-list">
                    <?php foreach ($data_list as $node){ ?>
                        <div class="col-md-3 col-xs-6 mb-2 product">
                            <div class="product-img">
                                <a href="<?php echo site_url("/product/{$node['slug']}") ?>" title="Post">
                                    <img  src="<?php echo \zap\helpers\ThumbHelper::thumb($node['image'],200,200); ?>" alt="Post" >
                                </a>
                            </div>

                            <div class="text-center">
                                <a href="<?php echo site_url("/product/{$node['slug']}") ?>" title="<?php echo $node['title'];?>" class="title fs-6"><?php echo $node['title'];?></a>
<!--                                <a href="#" title="产品价格" class="price"></a>-->
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div class="row text-center">
                    <?php echo $page->render(); ?>
                </div>
            </section>

        </div>
    </div>
</main>
