<?php
defined('IN_ZAP_CMS') or die('No permission to access');
$this->extend('layout/default'); ?>
<main class="site-main">
    <!-- Slider main container -->
    <div class="swiper">
        <!-- Additional required wrapper -->
        <div class="swiper-wrapper">
            <!-- Slides -->
            <div class="swiper-slide " >
                <a href="#"  style="background: url('/themes/basic/img/banner1.png') no-repeat center center;  ">
                </a>
            </div>
            <div class="swiper-slide">
                <a href="#"  style="background: url('/themes/basic/img/banner2.png') no-repeat center center; " >
                </a>
            </div>

        </div>
        <div class="swiper-pagination"></div>

        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>

    </div>

    <section class="boxes_area">
        <div class="container">
            <div class="row">
                <div class="col-sm-4">
                    <div class="box">
                        <h3>FIRST BOX TITLE</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        <i class="fa fa-cogs"></i>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="box">
                        <h3>SECOND BOX TITLE</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        <i class="fa fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="box">
                        <h3>THIRD BOX TITLE</h3>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        <i class="fa fa-clipboard"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="services">
        <h2 class="section-title">SERVICES</h2>
        <p class="desc">Praesent faucibus ipsum at sodales blandit</p>
        <div class="container">
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="media">
                        <div class="media-left media-middle">
                            <i class="fa fa-cogs"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">FIRST SERVICE TITLE</h4>
                            <p>Lorem ipsum dolor amet,consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="media">
                        <div class="media-left media-middle">
                            <i class="fa fa-user-md"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">SECOND SERVICE TITLE</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="media">
                        <div class="media-left media-middle">
                            <i class="fa fa-stethoscope"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">THIRD SERVICE TITLE</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="media">
                        <div class="media-left media-middle">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">FOURTH SERVICE TITLE</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="media">
                        <div class="media-left media-middle">
                            <i class="fa fa-file-text-o"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">FIFTH SERVICE TITLE</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <div class="media">
                        <div class="media-left media-middle">
                            <i class="fa fa-heartbeat"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">SIXTH SERVICE TITLE</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin id pulvinar magna. Aenean accumsan iaculis lorem, nec sodales lectus auctor tempor.</p>
                        </div>
                    </div>
                </div>
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
                                    <img src="<?php echo \zap\helpers\ThumbHelper::thumb($news['image'],200,200); ?>" alt="Post">
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