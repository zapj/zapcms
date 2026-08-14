<?php
defined('IN_ZAPCMS') or die('No permission to access');
$this->extend('layout/default');
$this->beginBlock('content');
/**
 * @var array $node
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
                            <article class="single-post">
                                <div class="entry-header">
                                    <h2 class="entry-title"><?php echo htmlspecialchars($node['title']); ?></h2>
                                </div><!-- /.entry-header -->
                                <div class="post-thumbnail">
                                    <img src="<?php echo \zapcms\helpers\ThumbHelper::thumb($node['image'], (int)\zapcms\services\NodeType::getConfig($node['node_type'] ?? 'article', 'detail_image_width', 750), (int)\zapcms\services\NodeType::getConfig($node['node_type'] ?? 'article', 'detail_image_height', 480)); ?>" class="img-responsive" alt="<?php echo htmlspecialchars($node['title']); ?>">
                                </div><!-- /.post-thumbnail -->
                                <div class="post-content">
                                    <?php echo $node['content']; ?>
                                </div><!-- /.post-content -->
                            </article><!-- /.single-post -->
                        </div>
                    </div>
                </div><!-- /.content-wrap -->
            </div>
        </div>
    </div>
<?php $this->endBlock(); ?>
