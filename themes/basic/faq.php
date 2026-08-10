<?php
defined('IN_ZAPCMS') or die('No permission to access');
$this->extend('layout/default');
$this->beginBlock('content');
/**
 * @var array $articles
 */
?>
    <?php echo $this->partial('partials/_breadcrumb'); ?>
    <div class="container">
        <div class="row">
            <?php echo $this->partial('partials/_sidebar'); ?>
            <div class="col-sm-9">
                <div class="content-wrap">
                    <div class="row">
                        <div class="col-sm-12">
                            <?php foreach ($articles as $article): ?>
                            <article class="single-post">
                                <div class="entry-header">
                                    <h1 class="entry-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                                </div>
                                <div class="post-content">
                                    <?php echo $article['content']; ?>
                                </div>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->endBlock(); ?>
