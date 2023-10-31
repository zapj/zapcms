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
<main class="site-main category-main">
    <div class="container">
        <div class="row">
            <aside class="sidebar col-sm-3">
                <div class="widget">
                    <h4><?php echo page()->node['title']; ?></h4>
                    <ul>
                        <?php foreach(page()->subCatalogList as $catalog){ ?>
                            <li <?php if(page()->nodeId == $catalog['id']){echo 'class="current"';} ?>
                            ><a href="<?php echo site_url("/{$catalog['slug']}"); ?>" title="<?php echo $catalog['title'];?>"><?php echo $catalog['title'];?></a></li>
                        <?php } ?>

                    </ul>
                </div>
            </aside>
            <section class="page col-sm-9">
                <h2 class="page-title"><?php echo page()->node['title']; ?></h2>
                <div class="entry">
                    <?php echo page()->node['content']; ?>
                </div>
            </section>

        </div>
    </div>
</main>
