<?php
defined('IN_ZAP_CMS') or die('No permission to access');
$this->extend('layout/default');
$banners = option_get_json('basic_home.slide','[]',true);
?>
<main class="site-main">
    <!-- Slider main container -->
    <div class="swiper">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
            <?php foreach($banners as $banner){ ?>
            <div class="swiper-slide " >
                <a href="<?php echo $banner['link'] ?? '#';?>"  style="background: url('<?php echo $banner['img_path'];?>') no-repeat center center; background-size: cover; ">
                </a>
            </div>
            <?php } ?>


        </div>
        <div class="swiper-pagination"></div>

        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>

    </div>

    <?php if(option("basic_home.about_us")): ?>
    <section class="about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12 text-center">
                    <h2 class="section-title">关于我们</h2>
                    <p class="desc">About Us</p>
                    <div class="about-content">
                        <?php echo option("basic_home.about_us"); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php if(option('basic_home.service_title')): ?>
    <section class="services">
        <div class="container">
            <h2 class="section-title"><?php echo option('basic_home.service_title'); ?></h2>
            <p class="desc"><?php echo option('basic_home.service_subtitle'); ?></p>
            <div class="row">
                <?php 
                $serviceCount = 0;
                for ($i = 1; $i < 7; $i++) { 
                    if(option("basic_home.service{$i}_title")) {
                        $serviceCount++;
                    }
                }
                $colClass = $serviceCount <= 3 ? 4 : ($serviceCount <= 4 ? 3 : 4);
                ?>
                <?php for ($i = 1; $i < 7; $i++) { 
                    if(!option("basic_home.service{$i}_title")) continue;
                ?>
                <div class="col-lg-<?php echo $colClass; ?> col-md-6 col-sm-12">
                    <div class="service-card">
                        <?php if(option("basic_home.service{$i}_icon")): ?>
                        <div class="service-icon">
                            <i class="<?php echo option("basic_home.service{$i}_icon"); ?>"></i>
                        </div>
                        <?php endif; ?>
                        <h3 class="service-title"><?php echo option("basic_home.service{$i}_title"); ?></h3>
                        <p class="service-desc"><?php echo option("basic_home.service{$i}_content"); ?></p>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
    
    <?php if(!empty($latestNews)): ?>
    <section class="home-area">
        <div class="container">
            <div class="section-header">
                <h2 class="sub_title">最新动态</h2>
                <p class="section-desc">Latest News</p>
            </div>
            <div class="home_list">
                <div class="row">
                    <?php foreach ($latestNews as $news){ ?>
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="news-card">
                            <a href="<?php echo site_url("/{$news['node_type']}/{$news['slug']}") ?>" class="news-link">
                                <div class="news-image">
                                    <img src="<?php echo \zap\cms\helpers\ThumbHelper::thumb($news['image'],400,300); ?>" alt="<?php echo $news['title']; ?>">
                                    <div class="news-overlay">
                                        <i class="fa fa-link"></i>
                                    </div>
                                </div>
                                <div class="news-content">
                                    <span class="news-date"><i class="fa fa-calendar"></i> <?php echo date('Y-m-d', strtotime($news['created_at'])); ?></span>
                                    <h3 class="news-title"><?php echo $news['title']; ?></h3>
                                    <p class="news-excerpt"><?php echo \zap\util\Str::truncate(strip_tags($news['content']),80); ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>
</main>

<style>
/* Index Page Styles */
.about-section {
    padding: 80px 0;
    background: linear-gradient(180deg, #fff 0%, #f8f9fa 100%);
}

.about-content {
    max-width: 900px;
    margin: 0 auto;
    font-size: 16px;
    line-height: 1.8;
    color: #666;
}

.about-content img {
    max-width: 100%;
    border-radius: 16px;
    margin: 20px 0;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
}

/* Service Card */
.service-card {
    background: #fff;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    margin-bottom: 30px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.05);
    transition: all 0.4s ease;
    border: 1px solid transparent;
}

.service-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.15);
    border-color: rgba(102, 126, 234, 0.2);
}

.service-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    transition: all 0.3s;
}

.service-card:hover .service-icon {
    transform: scale(1.1);
}

.service-icon i {
    font-size: 36px;
    color: #fff;
}

.service-title {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
}

.service-desc {
    font-size: 14px;
    color: #666;
    line-height: 1.8;
    margin-bottom: 0;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: 50px;
}

.section-header .sub_title {
    font-size: 28px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.section-header .section-desc {
    font-size: 14px;
    color: #999;
    letter-spacing: 3px;
    text-transform: uppercase;
}

/* News Card */
.news-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 30px;
    box-shadow: 0 5px 30px rgba(0,0,0,0.05);
    transition: all 0.4s ease;
}

.news-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 50px rgba(0,0,0,0.1);
}

.news-link {
    display: block;
    color: inherit;
}

.news-image {
    position: relative;
    overflow: hidden;
    height: 220px;
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s ease;
}

.news-card:hover .news-image img {
    transform: scale(1.1);
}

.news-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(102, 126, 234, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.news-card:hover .news-overlay {
    opacity: 1;
}

.news-overlay i {
    font-size: 40px;
    color: #fff;
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.news-card:hover .news-overlay i {
    transform: translateY(0);
}

.news-content {
    padding: 25px;
}

.news-date {
    font-size: 12px;
    color: #999;
    display: block;
    margin-bottom: 10px;
}

.news-date i {
    margin-right: 5px;
}

.news-title {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 12px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.news-excerpt {
    font-size: 14px;
    color: #666;
    line-height: 1.7;
    margin-bottom: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (max-width: 768px) {
    .about-section {
        padding: 40px 0;
    }
    
    .about-content {
        font-size: 14px;
        padding: 0 10px;
    }
    
    .service-card {
        padding: 25px 20px;
        margin-bottom: 20px;
    }
    
    .service-icon {
        width: 60px;
        height: 60px;
    }
    
    .service-icon i {
        font-size: 28px;
    }
    
    .service-title {
        font-size: 16px;
    }
    
    .news-image {
        height: 160px;
    }
    
    .news-content {
        padding: 15px;
    }
    
    .news-title {
        font-size: 15px;
    }
    
    .news-excerpt {
        font-size: 13px;
        -webkit-line-clamp: 2;
    }
    
    .section-header {
        margin-bottom: 30px;
    }
    
    .section-header .sub_title {
        font-size: 20px;
    }
    
    .section-header .section-desc {
        font-size: 12px;
    }
    
    .home-area {
        padding: 40px 0;
    }
}

/* 小屏手机 */
@media (max-width: 375px) {
    .about-section {
        padding: 30px 0;
    }
    
    .service-card {
        padding: 20px 15px;
    }
    
    .service-icon {
        width: 50px;
        height: 50px;
        margin-bottom: 15px;
    }
    
    .service-icon i {
        font-size: 24px;
    }
    
    .service-title {
        font-size: 15px;
    }
    
    .news-image {
        height: 140px;
    }
    
    .news-content {
        padding: 12px;
    }
    
    .news-title {
        font-size: 14px;
        margin-bottom: 8px;
    }
}
</style>

<script>
    const swiper = new Swiper('.swiper', {
        loop: true,
        speed: 1000,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
</script>
