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
                            <?php if (empty($data_list)): ?>
                            <p>未找到相关内容</p>
                            <?php else: ?>
                            <p>共找到 <?php echo count($data_list); ?> 条结果</p>
                            <?php foreach ($data_list as $node): ?>
                            <div class="search-item" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
                                <h4><a href="<?php echo site_url('/' . $node['slug']); ?>"><?php echo htmlspecialchars($node['title']); ?></a></h4>
                                <p><?php echo e(mb_substr(strip_tags($node['content'] ?? ''), 0, 200)) . '...'; ?></p>
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
