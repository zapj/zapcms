<?php
defined('IN_ZAP_CMS') or die('No permission to access');

use zap\cms\BreadCrumb;

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
<!--                <h2 class="category-title">文章列表</h2>-->
                <ul class="media-list">
                    <?php foreach ($data_list as $node){ ?>
                    <li class="media">
                        <div class="media-left">
                            <a href="#" title="Post">
                                <img class="media-object" src="<?php echo \zap\cms\helpers\ThumbHelper::thumb($node['image'],200,200); ?>" alt="Post">
                            </a>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading"><a href="<?php echo site_url("/article/{$node['slug']}") ?>" title="<?php echo $node['title'];?>"><?php echo $node['title'];?></a></h4>
                            <p class="text-muted"><?php echo \zap\util\Str::truncate(strip_tags($node['content']),200);?></p>
                            <aside class="meta category-meta">
                                <div class="pull-left ">
                                    <div class="arc-comment "><a href="#" title="阅读量" class="text-muted"><i class="fa fa-eye"></i> 阅读量  <?php echo empty($node['hits']) ? 0 :$node['hits']; ?> </a></div>
                                    <div class="arc-date text-muted"><?php echo date(Z_DATE,$node['pub_time']); ?></div>
                                </div>
                                <div class="pull-right">
                                    <ul class="arc-share">
                                        <li><a href="#0" title="Post"><i class="fa fa-wechat"></i></a></li>
                                        <li><a href="#0" title="Post"><i class="fa fa-weibo"></i></a></li>

                                    </ul>
                                </div>
                            </aside>
                        </div>
                    </li>
                    <?php } ?>



                </ul>
                <div class="row text-center">
                    <?php echo $page->render(); ?>
                </div>
            </section>

        </div>
    </div>
</main>