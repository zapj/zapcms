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
                <a href="<?php echo $banner['link'];?>"  style="background: url('<?php echo $banner['img_path'];?>') no-repeat center center;  ">
                </a>
            </div>
            <?php } ?>


        </div>
        <div class="swiper-pagination"></div>

        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>

    </div>

    <section class="services">
        <h2 class="section-title">关于我们</h2>
        <p class="desc">About US</p>
        <div class="container">
            <div class="row">

                    <div class="col-md-12 col-sm-12 col-xs-12 about-us-img-mlr-10px">
                        <?php echo option("basic_home.about_us"); ?>
                    </div>

            </div>
        </div>
    </section>


    <section class="services">
        <h2 class="section-title"><?php echo option('basic_home.service_title'); ?></h2>
        <p class="desc"><?php echo option('basic_home.service_subtitle'); ?></p>
        <div class="container">
            <div class="row">
                <?php for ($i = 1; $i < 7; $i++) { ?>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="media">
                        <div class="media-left media-middle" style="width: 40px">
                            <i class="<?php echo option("basic_home.service{$i}_icon"); ?>"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading"><?php echo option("basic_home.service{$i}_title"); ?></h4>
                            <p><?php echo option("basic_home.service{$i}_content"); ?></p>
                        </div>
                    </div>
                </div>
                <?php } ?>




            </div>
        </div>
    </section>
    <section class="home-area">
        <div class="home_content">
            <div class="container">
                <div class="row">
                    <div class="col-sm-12"><h2 class="sub_title">最新发布</h2></div>
                    <div class="home_list">
                        <ul>
                            <?php foreach ($latestNews as $news){ ?>
                            <li class="col-md-3 col-sm-6 col-xs-12">
                                <div class="thumbnail">
                                    <img src="<?php echo \zap\cms\helpers\ThumbHelper::thumb($news['image'],200,200); ?>" alt="<?php echo $news['image']; ?>">
                                    <div class="caption">
                                        <h3><a href="<?php echo site_url("/{$news['node_type']}/{$news['slug']}") ?>"><?php echo $news['title']; ?></a></h3>
                                        <p><?php  echo \zap\util\Str::truncate(strip_tags($news['content']),100); ?></p>

                                    </div>
                                </div>
                            </li>
                            <?php } ?>

                        </ul>
                    </div>



                </div>
            </div>
        </div>
    </section>
</main>
<script>
    const swiper = new Swiper('.swiper', {
        loop: true,

        // If we need pagination
        pagination: {
            el: '.swiper-pagination',
        },

        // Navigation arrows
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },


    });
</script>