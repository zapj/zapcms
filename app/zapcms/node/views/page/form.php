<?php

use zapcms\support\Asset;
use zap\facades\Url;

Asset::library('summernote');
Asset::library('datetimepicker');
Asset::library('jqueryvalidation');
!IS_AJAX && $this->extend('layouts/common');

$this->view->page_title = !empty($sub_title) ? $sub_title : ($title ?? '编辑');
// $this->view->page_subtitle = $title ?? '';
?>
<form id="zapForm">
    <input type="hidden" value="<?php echo $node->id; ?>" name="node_id">
    <input type="hidden" name="node[pub_time]" value="<?php echo $node->getPubTimeToDate(); ?>" />
    <input name="node[status]" id="node_status" type="hidden" value="<?php echo $node->status ?: \zapcms\models\Node::STATUS_PUBLISH;?>" />
    <input type="hidden" id="node_author_id" name="node[author_id]" value="<?php echo \zapcms\services\Auth::user('id') ?>">
    <input type="hidden" name="catalog[<?php echo $catalogId;?>]" value="<?php echo $catalog['level'];?>" />

    <div class="row g-3">
        <div class="col-lg-3">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-sitemap me-2"></i>栏目导航</h3>
                </div>
                <?php
                $this->include('default/sidebar','left_menu');
                echo $this->block('left_menu');
                ?>
            </div>
        </div>
        <div class="col-lg-9">
            <div class="card card-outline card-success">
                <div class="card-header ps-3 pt-0 pb-0 pe-0">
                    <ul class="nav nav-underline " role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab1" type="button">
                                <i class="fa fa-file-alt me-1"></i>常规
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab2" type="button">
                                <i class="fa fa-search me-1"></i>SEO
                            </button>
                        </li>
                    </ul>
                    
                </div>
                <div class="card-body p-3">
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab1">
                            <div class="mb-2">
                                <label for="node_title" class="form-label">标题 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="node[title]" id="node_title"
                                       placeholder="请输入标题" required value="<?php echo $node->title; ?>">
                            </div>
                            <div class="mb-2">
                                <label for="node_content" class="form-label">内容</label>
                                <textarea name="node[content]" id="node_content"
                                          class="form-control summernote"><?php echo $node->content; ?></textarea>
                            </div>
                            <div class="mb-0">
                                <label for="node_sort_order" class="form-label">排序</label>
                                <input type="number" class="form-control form-control-sm" name="node[sort_order]" id="node_sort_order"
                                       placeholder="越小越靠前" value="<?php echo $node->sort_order ?? 0; ?>" style="width:140px;">
                            </div>
                        </div>
                        <div class="tab-pane" id="tab2">
                            <div class="mb-2">
                                <label for="node_keywords" class="form-label">关键词</label>
                                <input type="text" class="form-control form-control-sm" name="node[keywords]" id="node_keywords"
                                       placeholder="多个用逗号分隔" value="<?php echo $node->keywords; ?>"/>
                            </div>
                            <div class="mb-2">
                                <label for="node_description" class="form-label">页面描述</label>
                                <textarea class="form-control form-control-sm" name="node[description]"
                                          id="node_description" rows="3"
                                          placeholder="建议控制在150字以内"><?php echo $node->description; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-success btn-sm" onclick="save();">
                        <i class="fa fa-save me-1"></i>保存
                    </button>
                    <?php if($node->add_time){ ?>
                    <span class="float-end text-muted small pt-1">
                        <i class="fa fa-clock me-1"></i><?php echo date('Y-m-d H:i',$node->pub_time); ?>
                        &nbsp;|&nbsp;
                        <i class="fa fa-edit me-1"></i><?php echo date('Y-m-d H:i',$node->update_time); ?>
                    </span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.nav-tabs .nav-link { padding: 0.5rem 1rem; }
.nav-tabs .nav-link.active { color: #198754; border-bottom-color: #198754; }
.note-editor { border-radius: var(--bs-border-radius); overflow: hidden; }
.note-editor.note-frame { border-color: var(--bs-border-color); }
.note-editor.note-frame .note-editing-area { min-height: 300px; }
.note-editor .note-toolbar { background: #f8f9fa; padding: 0.35rem 0.5rem; }
</style>

<script>
$(function(){
    $('#zapForm').validate({ignore:'', messages:{"node[title]":"标题必须填写"}});
});
function save() {
    const f = $('#zapForm');
    if (!f.valid()) { ZapToast.alert('请修改错误项，重新提交', {bgColor: bgDanger}); return; }
    const l = Zap.loading('正在保存，请稍后');
    $.ajax({
        url: '<?php echo Url::currentFull();?>',
        method: 'post', data: f.serialize(), dataType: 'json',
        success: function (d) {
            if (d.code === 0) {
                ZapToast.alert(d.msg, {bgColor: bgSuccess});
                <?php if($node->id){ ?>
                Zap.reload({callback:function(){ l.dispose(); createEditor(); }});
                <?php }else{ ?>
                location.href = d.redirect_to;
                <?php } ?>
            } else {
                ZapToast.alert(d.msg, {bgColor: bgDanger});
            }
        }
    });
}
</script>
<?php
!IS_AJAX && \zap\cms\Editor::instance()->create('.summernote', [
    'image_upload' => 'zapSendFile',
    'upload_url' => url_action('Upload@image')
]);
