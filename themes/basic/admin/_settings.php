<?php
defined('IN_ZAPCMS_ADMIN') or die('No permission');

/**
 * 首页设置
 *
 * 配置项：
 *   basic_home.slide - 首页轮播图（Swiper），JSON 数组，每项 {img_path, link, target}
 *      - img_path: 图片路径
 *      - link:     跳转链接
 *      - target:   打开方式，_blank 新窗口 / _self 当前页
 */

use zap\facades\Cache;

// 默认轮播图
$defaultSlides = [
    ['img_path' => '/themes/basic/img/banner1.png', 'link' => 'https://zapcms.cn', 'target' => '_blank'],
    ['img_path' => '/themes/basic/img/banner2.png', 'link' => 'https://zapcms.cn', 'target' => '_blank'],
];

// ---- 保存（配置保存到 options 表，键名 basic_home.slide） ----
if (req()->isPost()) {
    $imgPaths = (array) req()->post('slide_img', []);
    $links    = (array) req()->post('slide_link', []);
    $targets  = (array) req()->post('slide_target', []);
    $slides   = [];
    foreach ($imgPaths as $i => $imgPath) {
        $imgPath = trim((string) $imgPath);
        if ($imgPath === '') {
            continue;
        }
        $target = (string) ($targets[$i] ?? '');
        $slides[] = [
            'img_path' => $imgPath,
            'link'     => trim((string) ($links[$i] ?? '')),
            'target'   => ($target === '_blank' || $target === '_self') ? $target : '_blank',
        ];
    }
    $json = json_encode($slides, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    // 存在则更新、不存在则新增，避免 option_name 无唯一索引导致重复记录
    if (\zapcms\services\Option::get('basic_home.slide') === null) {
        \zapcms\services\Option::add('basic_home.slide', $json);
    } else {
        \zapcms\services\Option::update('basic_home.slide', $json);
    }
    Cache::delete('_opts_basic_home');
    echo '<div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <i class="fa-solid fa-circle-check me-1"></i> 设置已保存
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>';
}

// ---- 读取当前值（兼容旧数据：无 target 时默认 _blank） ----
$slides = option_get_json('basic_home.slide', $defaultSlides, true);
if (!is_array($slides)) {
    $slides = $defaultSlides;
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
            <div class="callout callout-info">
                <h5><i class="fa-solid fa-images me-1"></i> 首页轮播图（Swiper）</h5>
                <p class="mb-0">每张卡片对应一张轮播图，可自由增删。配置保存于 options 表：<code>basic_home.slide</code></p>
            </div>

            <div id="slideList" class="mb-3">
                <?php foreach ($slides as $index => $slide): ?>
                <div class="slide-row card card-body py-2 mb-2 shadow-none">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label small text-muted mb-1">图片路径</label>
                            <div class="input-group">
                                <input type="text" class="form-control" name="slide_img[]"
                                       id="slide_img_<?php echo $index; ?>"
                                       placeholder="/themes/basic/img/banner1.png"
                                       value="<?php echo htmlspecialchars((string) ($slide['img_path'] ?? '')); ?>">
                                <button type="button" class="btn btn-outline-primary btn-pick-image"
                                        data-target="slide_img_<?php echo $index; ?>" title="选择图片">
                                    <i class="fa-solid fa-folder-open"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small text-muted mb-1">跳转链接</label>
                            <input type="text" class="form-control" name="slide_link[]"
                                   placeholder="https://zapcms.cn"
                                   value="<?php echo htmlspecialchars((string) ($slide['link'] ?? '')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small text-muted mb-1">打开方式</label>
                            <div class="input-group">
                                <select class="form-select" name="slide_target[]">
                                    <option value="_blank" <?php echo (($slide['target'] ?? '_blank') === '_blank') ? 'selected' : ''; ?>>新窗口打开</option>
                                    <option value="_self" <?php echo (($slide['target'] ?? '_blank') === '_self') ? 'selected' : ''; ?>>当前页面</option>
                                </select>
                                <button type="button" class="btn btn-outline-danger btn-remove-slide" title="删除该图">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm btn-add-slide">
                <i class="fa-solid fa-plus me-1"></i>添加一张图片
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    var seq = <?php echo (int) count($slides); ?>;
    function rowHtml() {
        var id = 'slide_img_' + (seq++);
        return '<div class="slide-row card card-body py-2 mb-2 shadow-none">' +
            '<div class="row g-2 align-items-end">' +
            '<div class="col-md-5">' +
            '<label class="form-label small text-muted mb-1">图片路径</label>' +
            '<div class="input-group">' +
            '<input type="text" class="form-control" name="slide_img[]" id="' + id + '" placeholder="/themes/basic/img/banner1.png">' +
            '<button type="button" class="btn btn-outline-primary btn-pick-image" data-target="' + id + '" title="选择图片">' +
            '<i class="fa-solid fa-folder-open"></i></button>' +
            '</div></div>' +
            '<div class="col-md-4">' +
            '<label class="form-label small text-muted mb-1">跳转链接</label>' +
            '<input type="text" class="form-control" name="slide_link[]" placeholder="https://zap.cn">' +
            '</div>' +
            '<div class="col-md-3">' +
            '<label class="form-label small text-muted mb-1">打开方式</label>' +
            '<div class="input-group">' +
            '<select class="form-select" name="slide_target[]">' +
            '<option value="_blank">新窗口打开</option>' +
            '<option value="_self">当前页面</option>' +
            '</select>' +
            '<button type="button" class="btn btn-outline-danger btn-remove-slide" title="删除该图">' +
            '<i class="fa-solid fa-trash"></i></button>' +
            '</div></div>' +
            '</div></div>';
    }

    var list = document.getElementById('slideList');
    var addBtn = document.querySelector('.btn-add-slide');
    if (!list) return;

    addBtn && addBtn.addEventListener('click', function () {
        list.insertAdjacentHTML('beforeend', rowHtml());
    });

    list.addEventListener('click', function (e) {
        var pick = e.target.closest('.btn-pick-image');
        if (pick) {
            // 打开 ZAP 文件管理器，选中后自动填入对应图片路径 input
            if (window.Zap && typeof Zap.finder === 'function') {
                Zap.finder({target: '#' + pick.dataset.target, size: 'original', title: '选择轮播图片'});
            }
            return;
        }
        var btn = e.target.closest('.btn-remove-slide');
        if (btn) {
            var row = btn.closest('.slide-row');
            if (row) {
                row.parentNode.removeChild(row);
            }
        }
    });
})();
</script>
