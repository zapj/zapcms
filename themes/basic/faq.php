<?php use zap\BreadCrumb;

echo $this->extend('layout/default'); ?>
<div class="bread_area">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <?php BreadCrumb::instance()->display(); ?>
            </div>
        </div>
    </div>
</div>
<main class="site-main category-main">
    <div class="container">
        <div class="row">
            <aside class="sidebar col-sm-3">
                <div class="widget">
                    <h4><?php echo pageState()->node['title']; ?></h4>
                    <ul>
                        <?php foreach(pageState()->subCatalogList as $catalog){ ?>
                            <li <?php if(pageState()->nodeId == $catalog['id']){echo 'class="current"';} ?>
                            ><a href="<?php echo site_url("/{$catalog['slug']}"); ?>" title="<?php echo $catalog['title'];?>"><?php echo $catalog['title'];?></a></li>
                        <?php } ?>

                    </ul>
                </div>
            </aside>
            <section class="category-content col-sm-9">
                <h2 class="category-title">FAQ (常见问题)</h2>
                <div class="panel-group" id="faqList" role="tablist" aria-multiselectable="true">
                    <?php foreach($data_list as $data){ ?>
                    <div class="panel panel-default">
                        <div class="panel-heading" id="faq<?php echo $data['id'];?>" >
                            <h2 class="mb-0 panel-title">
                                <a class="btn btn-link" type="button" data-toggle="collapse" data-target="#faqContent<?php echo $data['id'];?>" aria-expanded="true" aria-controls="collapseOne">
                                    <?php echo $data['title'];?>
                                </a>
                            </h2>
                        </div>

                        <div id="faqContent<?php echo $data['id'];?>" class="collapse" aria-labelledby="faq<?php echo $data['id'];?>" data-parent="#faqList">
                            <div class="panel-body">
                                <?php echo $data['content'];?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                </div>
            </section>

        </div>
    </div>
</main>