<?php defined('IN_ZAP_CMS') or die('No permission to access'); ?>
<?php $this->extend('layout/default'); ?>
<?php $this->beginBlock('content'); ?>
    <?php echo $this->partial('partials/_breadcrumb'); ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-9">
                <div class="content-wrap">
                    <div class="row">
                        <div class="col-sm-12">
                            <article class="single-post">
                                <div class="entry-header">
                                    <h1 class="entry-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                                </div>
                                <div class="post-content">
                                    <?php echo $article['content']; ?>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $this->partial('partials/_sidebar'); ?>
        </div>
    </div>
<?php $this->endBlock(); ?>
