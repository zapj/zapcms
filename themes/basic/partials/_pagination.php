<?php defined('IN_ZAP_CMS') or die('No permission to access'); ?>
<?php if (!empty($page) && $page->total() > $page->perPage()): ?>
<div class="pagination-wrapper">
    <?php echo $page->render(); ?>
</div>
<?php endif; ?>
