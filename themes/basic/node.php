<?php defined('IN_ZAP_CMS') or die('No permission to access'); ?>
<?php $this->extend('layout/default'); ?>
<?php $this->beginBlock('content'); ?>
    <?php echo $this->partial('partials/_breadcrumb'); ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-9">
                <div class="content-wrap">
                    <div class="row">
                        <?php if (empty($articles)): ?>
                        <div class="col-sm-12">
                            <p>暂无内容</p>
                        </div>
                        <?php else: foreach ($articles as $article): ?>
                        <div class="col-sm-4">
                            <div class="post-content">
                                <a href="<?php echo site_url('/' . $article['slug']); ?>">
                                    <img class="img-responsive" src="<?php echo \zap\cms\helpers\ThumbHelper::thumb($article['image'], 270, 210); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" />
                                </a>
                                <div class="content-wrap">
                                    <h4><a href="<?php echo site_url('/' . $article['slug']); ?>"><?php echo htmlspecialchars($article['title']); ?></a></h4>
                                    <ul class="list-inline post-meta">
                                        <li><i class="fa fa-calendar"></i> <?php echo date('Y-m-d', strtotime($article['created_at'])); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <?php echo $this->partial('partials/_pagination', ['page' => $page ?? null]); ?>
                </div>
            </div>
            <?php echo $this->partial('partials/_sidebar'); ?>
        </div>
    </div>
<?php $this->endBlock(); ?>
