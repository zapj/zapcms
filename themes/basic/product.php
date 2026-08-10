<?php
defined('IN_ZAP_CMS') or die('No permission to access');
$this->extend('layout/default');
$this->beginBlock('content');
/**
 * @var array $article
 */
?>
    <?php echo $this->partial('partials/_breadcrumb'); ?>
    <div class="container">
        <div class="row">
            <?php echo $this->partial('partials/_sidebar'); ?>
            <div class="col-sm-9">
                <div class="content-wrap">
                    <div class="product-detail">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="product-gallery text-center">
                                    <?php if (!empty($article['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($article['image']); ?>" class="img-responsive" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <h1 class="product-title"><?php echo htmlspecialchars($article['title']); ?></h1>
                                <?php if (!empty($article['excerpt'])): ?>
                                <p class="product-excerpt text-muted"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                                <?php endif; ?>
                                <div class="product-meta">
                                    <?php if (!empty($article['pub_time'])): ?>
                                    <span class="text-muted"><i class="fa fa-calendar"></i> <?php echo date('Y-m-d', is_numeric($article['pub_time']) ? intval($article['pub_time']) : strtotime($article['pub_time'])); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($article['hits'])): ?>
                                    <span class="text-muted">&nbsp;&nbsp;<i class="fa fa-eye"></i> <?php echo intval($article['hits']); ?> 次浏览</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($article['content'])): ?>
                        <div class="product-content">
                            <h3 class="section-title">产品详情</h3>
                            <div class="post-content">
                                <?php echo $article['content']; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->endBlock(); ?>
