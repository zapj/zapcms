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
        <div class="row mb-20px">
            <aside class="sidebar col-sm-3">
                <div class="widget">
                    <h4><?php  $topCatalog = array_shift(page()->subCatalogList);
                                    echo $topCatalog['title'];
                                    $slugs[] = $topCatalog['slug'];
                                    ?></h4>
                    <ul>
                        <?php foreach(page()->subCatalogList as $catalog){ ?>
                            <li <?php if(page()->nodeId == $catalog['id']){echo 'class="current"';} ?>
                            ><a href="<?php echo site_url("/{$catalog['slug']}"); ?>" title="<?php echo $catalog['title'];?>"><?php echo $catalog['title'];?></a></li>
                        <?php } ?>

                    </ul>
                </div>
            </aside>
            <section class="page col-sm-9">
                <div class="row mb-20px">

                        <div class="col-md-6 col-6 mb-2">
                            <div class="product-img">
                                    <img  src="<?php echo theme_url(); ?>img/h1.jpeg" alt="Post" >

                            </div>


                        </div>
                         <div class="col-md-6 mb-2 text-left">
                             <h3><?php echo page()->node['title'];?></h3>
                        </div>
                </div>

                <ul class="nav nav-tabs mb-20px" id="myTab" role="tablist">
                    <li class="nav-item active">
                        <a class="nav-link " id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">产品介绍</a>
                    </li>

                </ul>

                <div class="tab-content " id="myTabContent">
                    <div class="tab-pane show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <?php echo page()->node['content']; ?>
                    </div>

                </div>
            </section>

        </div>
    </div>
</main>