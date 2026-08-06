<?php

use zap\cms\Asset;
use zap\facades\Url;

Asset::library('summernote');
Asset::library('datetimepicker');
Asset::library('jqueryvalidation');
register_scripts(base_url('/assets/plugins/zapuploader.js'));

!IS_AJAX && $this->extend('layouts/common');

$this->view->page_title = !empty($sub_title) ? $sub_title : ($title ?? '编辑');
$this->view->page_subtitle = $title ?? '';
?>
<form id="zapForm" method="post">
    <input type="hidden" value="<?php echo $node->id; ?>" name="node_id">
    <input type="hidden" name="node[pub_time]" value="<?php echo $node->getPubTimeToDate(); ?>" />
    <input name="node[status]" id="node_status" type="hidden" value="<?php echo $node->status ?: \zap\cms\models\Node::STATUS_PUBLISH;?>" />
    <input type="hidden" id="node_author_id" name="node[author_id]" value="<?php echo \zap\cms\Auth::user('id') ?>">

    <div class="row g-3">
        <div class="col-lg-9">
            <div class="card card-outline card-success">
                <div class="card-header d-flex align-items-center ps-3 pt-0 pb-0 pe-0">
                    <ul class="nav nav-underline me-auto" role="tablist">
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
                    <div class="card-tools">
                        <a class="btn btn-tool btn-sm" href="<?php echo url_action("Node@{$_controller}", $_GET); ?>">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>
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
                            <div class="row g-2">
                                <div class="col-auto">
                                    <label for="node_hits" class="form-label">点击量</label>
                                    <input type="number" class="form-control form-control-sm" name="node[hits]" id="node_hits"
                                           value="<?php echo $node->hits ?? 0; ?>" style="width:100px;">
                                </div>
                                <div class="col-auto">
                                    <label for="node_sort_order" class="form-label">排序</label>
                                    <input type="number" class="form-control form-control-sm" name="node[sort_order]" id="node_sort_order"
                                           value="<?php echo $node->sort_order ?? 0; ?>" style="width:100px;">
                                </div>
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
                    <?php if($node->update_time){ ?>
                    <span class="float-end text-muted small pt-1">
                        <i class="fa fa-clock me-1"></i><?php echo date('Y-m-d H:i',$node->pub_time); ?>
                        &nbsp;|&nbsp;
                        <i class="fa fa-edit me-1"></i><?php echo date('Y-m-d H:i',$node->update_time); ?>
                    </span>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-paper-plane me-2"></i>发布</h3>
                </div>
                <div class="card-body p-2">
                    <div class="mb-2">
                        <label for="node_status_select" class="form-label small mb-1">状态</label>
                        <select class="form-select form-select-sm" name="node[status]" id="node_status_select">
                            <?php foreach($node->getStatus() as $id => $title){
                                if($id == \zap\cms\models\Node::STATUS_SOFT_DELETE or $id == \zap\cms\models\Node::STATUS_TRASH){ continue; } ?>
                                <option value="<?php echo $id;?>" <?php echo $node->status==$id?'selected':null ;?> ><?php echo $title;?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label for="node_pub_time" class="form-label small mb-1">发布时间</label>
                        <input type="text" class="form-control form-control-sm datetimepicker" name="node[pub_time]"
                               id="node_pub_time" value="<?php echo $node->getPubTimeToDate(); ?>" required/>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small mb-1">发布人</label>
                        <input type="text" class="form-control form-control-sm form-control-plaintext" readonly value="<?php echo \zap\cms\Auth::user('full_name') ?>">
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-folder-open me-2"></i>分类</h3>
                </div>
                <div class="card-body p-2 catalog-list">
                    <?php
                    while($catalog = array_shift($catalogList)){
                        $indent = $catalog['level'] * 1;
                        ?>
                        <div class="form-check mb-1" style="padding-left: <?php echo $indent + 1.5; ?>em;">
                            <input class="form-check-input" type="checkbox" name="catalog[<?php echo $catalog['id']; ?>]"
                                   <?php echo !empty($node_relations[$catalog['id']]) ? 'checked' : '' ?>
                                   value="<?php echo $catalog['level'];?>"
                                   id="catalog-<?php echo $catalog['id'];?>">
                            <label class="form-check-label small" for="catalog-<?php echo $catalog['id'];?>">
                                <?php echo $catalog['title'];?>
                            </label>
                        </div>
                        <?php
                        if(isset($catalog['children'])){
                            while ($children = array_pop($catalog['children'])){
                                array_unshift($catalogList,$children);
                            }
                        }
                    }
                    ?>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-image me-2"></i>主图</h3>
                </div>
                <div class="card-body text-center p-2">
                    <img src="<?php echo \zap\cms\helpers\ThumbHelper::thumb($node['image'],136,136); ?>"
                         class="img-thumbnail rounded" id="node-image-thumb" style="max-height:140px;" alt=""/>
                    <input type="hidden" name="node[image]" id="node-image" value="<?php echo $node['image']; ?>" />
                </div>
                <div class="card-footer p-2 d-flex gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill"
                            data-zap-toggle="image" data-zap-target="#node-image|#node-image-thumb">
                        <i class="fa fa-upload me-1"></i>选择
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeImage()">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<style>
.nav-tabs .nav-link { padding: 0.5rem 1rem; }
.nav-tabs .nav-link.active { color: #198754; border-bottom-color: #198754; }
.catalog-list { max-height: 220px; overflow-y: auto; }
.note-editor { border-radius: var(--bs-border-radius); overflow: hidden; }
.note-editor.note-frame { border-color: var(--bs-border-color); }
.note-editor.note-frame .note-editing-area { min-height: 300px; }
.note-editor .note-toolbar { background: #f8f9fa; padding: 0.35rem 0.5rem; }
</style>

<script>
$(function(){
    $.datetimepicker.setLocale('zh');
    $('.datetimepicker').datetimepicker({ format: 'Y-m-d H:i:s' });
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
                ZapToast.alert(d.msg, {bgColor: bgSuccess, callback: function(){
                    <?php if($node->id){ ?>location.reload();<?php }else{ ?>location.href = d.redirect_to;<?php } ?>
                }});
            } else { ZapToast.alert(d.msg, {bgColor: bgDanger}); }
        }
    }).always(function(){ l.dispose(); });
}
function removeImage() {
    $('#node-image').val('');
    $('#node-image-thumb').attr('src','').hide();
}
</script>
<?php
\zap\cms\Editor::instance()->create('.summernote', [
    'image_upload' => 'zapSendFile',
    'upload_url' => url_action('Upload@image')
]);
