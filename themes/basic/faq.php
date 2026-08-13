<?php
defined('IN_ZAPCMS') or die('No permission to access');
$this->extend('layout/default');
$this->beginBlock('content');
/**
 * @var array $nodes
 */
?>
    <?php echo $this->partial('partials/_breadcrumb'); ?>
    <div class="container">
        <div class="row">
            <?php echo $this->partial('partials/_sidebar'); ?>
            <div class="col-sm-9">
                <div class="content-wrap faq-page">
                    <!-- 搜索框 & 工具栏 -->
                    <div class="faq-toolbar">
                        <div class="faq-search-wrap">
                            <i class="fa fa-search faq-search-icon"></i>
                            <input type="text" id="faqSearch" class="faq-search-input" placeholder="搜索常见问题…" autocomplete="off" />
                            <span class="faq-search-clear" id="faqSearchClear" style="display:none;">&times;</span>
                        </div>
                        <button type="button" class="faq-toggle-all" id="faqToggleAll" title="展开全部">
                            <i class="fa fa-plus-square-o"></i> 展开全部
                        </button>
                    </div>

                    <!-- 搜索结果提示 -->
                    <div class="faq-no-result" id="faqNoResult" style="display:none;">
                        <i class="fa fa-meh-o"></i>
                        <p>没有找到匹配的问题，请尝试其他关键词</p>
                    </div>

                    <!-- FAQ 列表 -->
                    <div class="faq-list" id="faqList">
                        <?php
                        $lastCategory = null;
                        foreach ($nodes as $index => $node):
                            $catTitle = $node['category_title'] ?? '';
                            $artId = md5($node['title']);
                        ?>
                            <?php if ($catTitle && $catTitle !== $lastCategory): ?>
                                <?php if ($lastCategory !== null): ?>
                                    </div><!-- /.faq-category-body -->
                                <?php endif; ?>
                                <div class="faq-category" data-category="<?php echo htmlspecialchars($catTitle); ?>">
                                    <div class="faq-category-header">
                                        <i class="fa fa-folder-open"></i>
                                        <span><?php echo htmlspecialchars($catTitle); ?></span>
                                    </div>
                                    <div class="faq-category-body">
                            <?php $lastCategory = $catTitle; ?>
                            <?php endif; ?>

                            <div class="faq-item" data-keywords="<?php echo htmlspecialchars($node['title'] . ' ' . strip_tags($node['content'] ?? '')); ?>">
                                <div class="faq-question" role="button" tabindex="0" id="faq-<?php echo $artId; ?>">
                                    <span class="faq-q-icon">Q</span>
                                    <span class="faq-q-text"><?php echo htmlspecialchars($node['title']); ?></span>
                                    <i class="fa fa-chevron-down faq-chevron"></i>
                                </div>
                                <div class="faq-answer" id="faq-answer-<?php echo $artId; ?>">
                                    <div class="faq-answer-inner">
                                        <span class="faq-a-icon">A</span>
                                        <div class="faq-a-content">
                                            <?php echo $node['content']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if ($lastCategory !== null): ?>
                                    </div><!-- /.faq-category-body -->
                                </div><!-- /.faq-category -->
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var $list = $('#faqList');
        var $search = $('#faqSearch');
        var $clear = $('#faqSearchClear');
        var $noResult = $('#faqNoResult');
        var $toggleAll = $('#faqToggleAll');
        var allExpanded = false;

        // ---- 折叠 / 展开单个 ----
        $list.on('click', '.faq-question', function() {
            var $item = $(this).closest('.faq-item');
            var $answer = $item.find('.faq-answer');
            var isOpen = $item.hasClass('open');
            if (isOpen) {
                $item.removeClass('open');
                $answer.stop(true, false).slideUp(300);
            } else {
                $item.addClass('open');
                $answer.stop(true, false).slideDown(300);
            }
            updateToggleBtn();
        });

        // ---- 展开 / 收起全部 ----
        $toggleAll.on('click', function() {
            allExpanded = !allExpanded;
            var $items = $list.find('.faq-item:visible');
            if (allExpanded) {
                $items.addClass('open').find('.faq-answer').stop(true, false).slideDown(300);
                $toggleAll.html('<i class="fa fa-minus-square-o"></i> 收起全部');
            } else {
                $items.removeClass('open').find('.faq-answer').stop(true, false).slideUp(300);
                $toggleAll.html('<i class="fa fa-plus-square-o"></i> 展开全部');
            }
        });

        function updateToggleBtn() {
            var totalVisible = $list.find('.faq-item:visible').length;
            var openVisible = $list.find('.faq-item.open:visible').length;
            allExpanded = totalVisible > 0 && openVisible === totalVisible;
            if (allExpanded) {
                $toggleAll.html('<i class="fa fa-minus-square-o"></i> 收起全部');
            } else {
                $toggleAll.html('<i class="fa fa-plus-square-o"></i> 展开全部');
            }
        }

        // ---- 搜索过滤 ----
        $search.on('input', function() {
            var val = $.trim(this.value).toLowerCase();
            if (val.length > 0) {
                $clear.show();
            } else {
                $clear.hide();
            }
            var hasResult = false;
            $list.find('.faq-item').each(function() {
                var $item = $(this);
                var kw = $item.data('keywords') || '';
                var matched = kw.toLowerCase().indexOf(val) !== -1;
                $item.toggle(matched);
                if (matched) hasResult = true;
            });

            // 隐藏 / 显示空分类
            $list.find('.faq-category').each(function() {
                var $cat = $(this);
                var visibleItems = $cat.find('.faq-item:visible').length;
                $cat.toggle(visibleItems > 0);
            });

            $noResult.toggle(!hasResult);
            // 搜索完成自动展开匹配项
            if (val.length > 0) {
                $list.find('.faq-item:visible').each(function() {
                    var $item = $(this);
                    if (!$item.hasClass('open')) {
                        $item.addClass('open');
                        $item.find('.faq-answer').stop(true, false).show();
                    }
                });
            }
            updateToggleBtn();
        });

        $clear.on('click', function() {
            $search.val('').trigger('input').focus();
        });

        // ---- URL hash 直接定位到某个 FAQ ----
        function openByHash() {
            var hash = window.location.hash;
            if (hash && hash.indexOf('#faq-') === 0) {
                var $target = $(hash);
                if ($target.length) {
                    var $item = $target.closest('.faq-item');
                    if (!$item.hasClass('open')) {
                        $item.addClass('open');
                        $item.find('.faq-answer').show();
                    }
                    // 滚动到目标位置
                    $('html, body').animate({ scrollTop: $target.offset().top - 100 }, 400);
                    updateToggleBtn();
                }
            }
        }
        openByHash();
        $(window).on('hashchange', openByHash);

        // ---- 键盘支持 ----
        $list.on('keydown', '.faq-question', function(e) {
            if (e.which === 13 || e.which === 32) { // Enter / Space
                e.preventDefault();
                $(this).click();
            }
        });
    })();
    </script>
<?php $this->endBlock(); ?>
