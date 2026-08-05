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
                <div class="product-grid">
                    <?php foreach ($data_list as $node){ ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="<?php echo site_url("/product/{$node['slug']}") ?>">
                                    <img src="<?php echo \zap\cms\helpers\ThumbHelper::thumb($node['image'],400,400); ?>" alt="<?php echo $node['title'];?>">
                                    <div class="product-overlay">
                                        <i class="fa fa-link"></i>
                                    </div>
                                </a>
                            </div>
                            <div class="product-info">
                                <h4 class="product-title">
                                    <a href="<?php echo site_url("/product/{$node['slug']}") ?>" title="<?php echo $node['title'];?>"><?php echo $node['title'];?></a>
                                </h4>
                            </div>
                        </div>
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
/* Product Grid Styles */
.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
}

.product-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.product-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.12);
}

.product-image {
    position: relative;
    overflow: hidden;
    aspect-ratio: 1;
}

.product-image a {
    display: block;
    height: 100%;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(102,126,234,0.85) 0%, rgba(118,75,162,0.85) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-overlay i {
    font-size: 36px;
    color: #fff;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.product-info {
    padding: 16px;
    text-align: center;
}

.product-title {
    margin: 0;
    font-size: 15px;
    font-weight: 500;
    line-height: 1.4;
}

.product-title a {
    color: #667eea;
    transition: color 0.3s ease;
}

.product-title a:hover {
    color: #764ba2;
    text-decoration: none;
}

.pagination-wrapper {
    margin-top: 40px;
    display: flex;
    justify-content: center;
    grid-column: 1 / -1;
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
@media (max-width: 992px) {
    .product-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
    }
}

@media (max-width: 768px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .product-info {
        padding: 12px;
    }

    .product-title {
        font-size: 14px;
    }

    .product-overlay i {
        font-size: 28px;
    }
}

@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }

    .product-image {
        aspect-ratio: 1;
    }

    .product-info {
        padding: 10px 8px;
    }

    .product-title {
        font-size: 13px;
    }
}
</style>
