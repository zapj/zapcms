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
                <div class="article-list">
                    <?php foreach ($data_list as $node){ ?>
                    <article class="article-card">
                        <div class="article-image">
                            <a href="<?php echo site_url("/article/{$node['slug']}") ?>">
                                <img src="<?php echo \zap\cms\helpers\ThumbHelper::thumb($node['image'],400,280); ?>" alt="<?php echo $node['title'];?>">
                                <div class="article-overlay">
                                    <i class="fa fa-link"></i>
                                </div>
                            </a>
                        </div>
                        <div class="article-body">
                            <h3 class="article-title">
                                <a href="<?php echo site_url("/article/{$node['slug']}") ?>" title="<?php echo $node['title'];?>"><?php echo $node['title'];?></a>
                            </h3>
                            <p class="article-excerpt"><?php echo \zap\util\Str::truncate(strip_tags($node['content']),160);?></p>
                            <div class="article-meta">
                                <span class="meta-item"><i class="fa fa-calendar"></i> <?php echo date('Y-m-d',$node['pub_time']); ?></span>
                                <span class="meta-item"><i class="fa fa-eye"></i> <?php echo empty($node['hits']) ? 0 : $node['hits']; ?></span>
                                <a href="<?php echo site_url("/article/{$node['slug']}") ?>" class="read-more">阅读全文 <i class="fa fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </article>
                    <?php } ?>
                </div>
                <div class="pagination-wrapper">
                    <?php echo $page->render(); ?>
                </div>
            </section>

        </div>
    </div>
</main>

<style>
/* Article List Styles */
.article-list {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.article-card {
    display: flex;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.article-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}

.article-image {
    flex: 0 0 280px;
    position: relative;
    overflow: hidden;
}

.article-image a {
    display: block;
    height: 100%;
}

.article-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.article-card:hover .article-image img {
    transform: scale(1.08);
}

.article-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(102,126,234,0.8) 0%, rgba(118,75,162,0.8) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.article-overlay i {
    font-size: 32px;
    color: #fff;
}

.article-card:hover .article-overlay {
    opacity: 1;
}

.article-body {
    flex: 1;
    padding: 24px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.article-title {
    margin: 0 0 12px;
    font-size: 18px;
    font-weight: 600;
    line-height: 1.4;
}

.article-title a {
    color: #667eea;
    transition: color 0.3s ease;
}

.article-title a:hover {
    color: #764ba2;
    text-decoration: none;
}

.article-excerpt {
    color: #666;
    font-size: 14px;
    line-height: 1.8;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.article-meta {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-top: 12px;
    border-top: 1px solid #eee;
}

.meta-item {
    color: #999;
    font-size: 13px;
}

.meta-item i {
    margin-right: 5px;
    color: #667eea;
}

.read-more {
    margin-left: auto;
    color: #667eea;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.read-more:hover {
    color: #764ba2;
    text-decoration: none;
}

.read-more i {
    margin-left: 5px;
    transition: transform 0.3s ease;
}

.read-more:hover i {
    transform: translateX(3px);
}

.pagination-wrapper {
    margin-top: 30px;
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination {
    margin: 0;
}

.pagination-wrapper .pagination li a,
.pagination-wrapper .pagination li span {
    border: none;
    border-radius: 8px;
    margin: 0 3px;
    color: #666;
    transition: all 0.3s ease;
}

.pagination-wrapper .pagination li a:hover,
.pagination-wrapper .pagination li.active span {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .article-card {
        flex-direction: column;
    }

    .article-image {
        flex: none;
        height: 200px;
    }

    .article-body {
        padding: 16px;
    }

    .article-title {
        font-size: 16px;
    }

    .article-excerpt {
        font-size: 13px;
        -webkit-line-clamp: 2;
    }

    .article-meta {
        flex-wrap: wrap;
        gap: 12px;
    }

    .read-more {
        margin-left: 0;
    }
}
</style>
