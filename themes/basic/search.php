<?php defined('IN_ZAPCMS') or die('No permission to access'); ?>
<?php $this->extend('layout/default'); ?>
<?php $this->beginBlock('content'); ?>
    <?php echo $this->partial('partials/_breadcrumb'); ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="content-wrap">
                    <div class="row">
                        <div class="col-sm-12">
                            <form method="get" action="<?php echo site_url('/search'); ?>">
                                <div class="input-group" style="margin-bottom: 20px;">
                                    <input type="text" name="q" class="form-control" placeholder="请输入搜索关键词" value="<?php echo htmlspecialchars($query ?? ''); ?>">
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary" type="submit">搜索</button>
                                    </span>
                                </div>
                            </form>
                            <?php if (empty($articles)): ?>
                            <p>未找到相关内容</p>
                            <?php else: ?>
                            <p>共找到 <?php echo count($articles); ?> 条结果</p>
                            <?php foreach ($articles as $article): ?>
                            <div class="search-item" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                                <h4><a href="<?php echo site_url('/' . $article['slug']); ?>"><?php echo htmlspecialchars($article['title']); ?></a></h4>
                                <p><?php echo htmlspecialchars(mb_substr(strip_tags($article['content'] ?? ''), 0, 200)) . '...'; ?></p>
                            </div>
                            <?php endforeach; ?>
                            <?php echo $this->partial('partials/_pagination', ['page' => $page ?? null]); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $this->endBlock(); ?>
