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
<main class="site-main page-main">
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
            <section class="page col-sm-9">
                <h2 class="page-title"><?php echo pageState()->node['title']; ?></h2>
                <div class="entry">
                    <?php echo pageState()->node['content']; ?>
                </div>
            </section>

        </div>
    </div>
</main>