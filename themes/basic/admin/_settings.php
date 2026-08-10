<?php
defined('IN_ZAPCMS_ADMIN') or die('No permission');

/**
 * 首页设置
 */

if (req()->isPost()) {
    $settings = themeSettings();
    $settings->set('homeTitle', req()->post('homeTitle'));
    $settings->set('homeKeywords', req()->post('homeKeywords'));
    $settings->set('homeDescription', req()->post('homeDescription'));
    $settings->save();
    echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i> 设置已保存
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}
?>

<div class="card card-outline card-success">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fa-solid fa-home me-2"></i>首页设置
        </h5>
        <div class="card-tools">
            <button type="submit" form="homeSettingsForm" class="btn btn-sm btn-success">
                <i class="fa-solid fa-floppy-disk me-1"></i>保存设置
            </button>
        </div>
    </div>
    <div class="card-body">
        <form id="homeSettingsForm" method="post" action="<?php echo url_action('theme@settings', ['page' => '_settings']); ?>">
            <div class="mb-3 row">
                <label for="homeTitle" class="col-sm-2 col-form-label">网站标题</label>
                <div class="col-sm-10">
                    <input type="text" class="form-control" id="homeTitle" name="homeTitle"
                           value="<?php echo htmlspecialchars(themeSettings()->get('homeTitle', '')); ?>">
                </div>
            </div>
            <div class="mb-3 row">
                <label for="homeKeywords" class="col-sm-2 col-form-label">网站关键词</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="homeKeywords" name="homeKeywords" rows="2"><?php echo htmlspecialchars(themeSettings()->get('homeKeywords', '')); ?></textarea>
                    <small class="form-text text-muted">多个关键词用英文逗号分隔</small>
                </div>
            </div>
            <div class="mb-3 row">
                <label for="homeDescription" class="col-sm-2 col-form-label">网站描述</label>
                <div class="col-sm-10">
                    <textarea class="form-control" id="homeDescription" name="homeDescription" rows="3"><?php echo htmlspecialchars(themeSettings()->get('homeDescription', '')); ?></textarea>
                    <small class="form-text text-muted">简要描述网站内容，建议不超过 160 个字符</small>
                </div>
            </div>
        </form>
    </div>
</div>
