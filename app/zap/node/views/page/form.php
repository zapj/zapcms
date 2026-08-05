<?php

use zap\cms\Asset;
use zap\cms\BreadCrumb;
use zap\facades\Url;

Asset::library('summernote');
Asset::library('datetimepicker');
Asset::library('jqueryvalidation');
!IS_AJAX && $this->extend('layouts/common');
?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 zap-top-bar">
    <div class="container-fluid">
        <?php BreadCrumb::instance()->display('<li class="d-block d-md-none d-lg-none"><i class="fa fa-bars me-1" onclick="$(\'#nodeleftsidebar\').toggleClass(\'d-none\');"></i> </li>') ; ?>
        <div class="text-end">
            <a class="btn btn-secondary btn-sm" href="<?php echo req()->prevUrl(url_action('Node@page'));?>">
                <i class="fa fa-arrow-left me-1"></i> 返回
            </a>
            <button type="button" class="btn btn-success btn-sm" onclick="save();">
                <i class="fa fa-save me-1"></i> 保存
            </button>
        </div>
    </div>
</nav>

<form id="zapForm">
    <input type="hidden" value="<?php echo $node->id; ?>" name="node_id">
    <input type="hidden" name="node[pub_time]" value="<?php echo $node->getPubTimeToDate(); ?>" />
    <input name="node[status]" id="node_status" type="hidden" value="<?php echo $node->status ?: \zap\cms\models\Node::STATUS_PUBLISH;?>" />
    <input type="hidden" id="node_author_id" name="node[author_id]" value="<?php echo \zap\cms\Auth::user('id') ?>">
    <input type="hidden" name="catalog[<?php echo $catalogId;?>]" value="<?php echo $catalog['level'];?>" />

    <main class="container-fluid zap-main">
        <div class="row">
            <?php
            $this->include('default/sidebar','left_menu');
            echo $this->block('left_menu');
            ?>
            <div class="col-md-9 mb-3">
                <!-- 主内容卡片 -->
                <div class="card shadow-sm">
                    <!-- Tabs 导航 -->
                    <div class="card-header p-0">
                        <ul class="nav nav-tabs border-0" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#zapTabPanel1" type="button">
                                    <i class="fa fa-file-alt me-1"></i>常规信息
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#zapTabPanel2" type="button">
                                    <i class="fa fa-search me-1"></i>SEO
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tabs 内容 -->
                    <div class="tab-content" id="zapTabContent">
                        <!-- 常规信息 -->
                        <div class="tab-pane fade show active" id="zapTabPanel1" role="tabpanel">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="node_title" class="form-label fw-medium">
                                        <i class="fa fa-heading text-success me-1"></i>标题
                                    </label>
                                    <input type="text" class="form-control form-control-lg" name="node[title]" id="node_title"
                                           placeholder="请输入文章标题" required value="<?php echo $node->title; ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="node_content" class="form-label fw-medium">
                                        <i class="fa fa-edit text-success me-1"></i>内容
                                    </label>
                                    <textarea name="node[content]" id="node_content"
                                              class="form-control summernote"><?php echo $node->content; ?></textarea>
                                </div>

                                <!-- 更多设置折叠 -->
                                <div class="border-top pt-3 mt-4">
                                    <a class="btn btn-link text-success p-0 text-decoration-none collapsed" 
                                       data-bs-toggle="collapse" href="#morenodesettings" role="button">
                                        <i class="fa fa-cog me-1"></i>更多设置 <i class="fa fa-chevron-down fa-xs"></i>
                                    </a>
                                </div>
                                <div class="collapse" id="morenodesettings">
                                    <div class="row g-3 mt-2">
                                        <div class="col-md-6">
                                            <label for="node_sort_order" class="form-label">排序</label>
                                            <input type="number" class="form-control" name="node[sort_order]" id="node_sort_order"
                                                   placeholder="数值越小越靠前" value="<?php echo $node->sort_order ?? 0; ?>"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SEO -->
                        <div class="tab-pane fade" id="zapTabPanel2" role="tabpanel">
                            <div class="card-body">
                                <div class="alert alert-info py-2 px-3 mb-3">
                                    <i class="fa fa-info-circle me-1"></i>
                                    <small>优化搜索排名，让搜索引擎更好地收录您的内容</small>
                                </div>

                                <div class="mb-3">
                                    <label for="node_keywords" class="form-label fw-medium">
                                        <i class="fa fa-tags text-success me-1"></i>关键词
                                    </label>
                                    <input type="text" class="form-control" name="node[keywords]" id="node_keywords"
                                           placeholder="多个关键词用逗号分隔" value="<?php echo $node->keywords; ?>"/>
                                    <small class="text-muted">例如：PHP教程,CMS系统,网站开发</small>
                                </div>

                                <div class="mb-3">
                                    <label for="node_description" class="form-label fw-medium">
                                        <i class="fa fa-file-lines text-success me-1"></i>页面描述
                                    </label>
                                    <textarea type="text" class="form-control" name="node[description]"
                                              id="node_description" rows="3"
                                              placeholder="简要描述页面内容，建议控制在150字以内"><?php echo $node->description; ?></textarea>
                                    <small class="text-muted">建议填写简洁、有吸引力的描述</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 底部信息 -->
                    <?php if($node->add_time){ ?>
                    <div class="card-footer bg-light">
                        <div class="row g-2 text-muted small">
                            <div class="col-auto">
                                <i class="fa fa-clock me-1"></i>发布时间: <?php echo date(Z_DATE_TIME,$node->pub_time); ?>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-edit me-1"></i>更新: <?php echo date(Z_DATE_TIME,$node->update_time); ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </main>
</form>

<style>
/* 编辑页面样式 */
.zap-main .card {
    border-radius: 12px;
    overflow: hidden;
}

/* Tabs 样式 - 绿色主题 */
.nav-tabs {
    background: #f8fafc;
    padding: 0.75rem 1rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.nav-tabs .nav-item {
    margin-bottom: -1px;
}

.nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #6b7280;
    font-weight: 500;
    padding: 0.75rem 1.25rem;
    border-radius: 8px 8px 0 0;
    transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
    color: #10b981;
    background: rgba(16, 185, 129, 0.05);
}

.nav-tabs .nav-link.active {
    color: #10b981;
    background: #fff;
    border-bottom-color: #10b981;
}

/* 表单样式 */
.form-label {
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    color: #374151;
}

.form-control {
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 0.625rem 0.875rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-control-lg {
    font-size: 1.1rem;
    padding: 0.75rem 1rem;
}

/* 折叠区域 */
.collapse {
    transition: all 0.3s ease;
}

/* 移动端适配 */
@media (max-width: 767px) {
    .card-header {
        padding: 0.75rem 1rem;
    }
    
    .nav-tabs {
        padding: 0.5rem 0.75rem 0;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }
    
    .nav-tabs .nav-link i {
        display: none;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .form-control-lg {
        font-size: 1rem;
    }
    
    .btn {
        font-size: 0.875rem;
    }
}

/* summernote 编辑器优化 */
.note-editor {
    border-radius: 8px;
    overflow: hidden;
}

.note-editor .note-toolbar {
    background: #f8fafc;
    padding: 0.5rem;
}

.note-editor.note-frame {
    border: 1px solid #d1d5db;
}

.note-editor.note-frame .note-editing-area {
    min-height: 300px;
}
</style>

<script>
    $(document).ready(function () {
        // 表单验证
        $('#zapForm').validate({
            ignore: '',
            messages: {
                "node[title]": "标题必须填写"
            }
        });
    });

    function save() {
        const zapForm = $('#zapForm');
        if (!zapForm.valid()) {
            ZapToast.alert('请修改错误项，重新提交', {bgColor: bgDanger, position: Toast_Pos_Center});
            return false;
        }
        zapload = Zap.loading('正在保存，请稍后');
        $.ajax({
            url: '<?php echo Url::current();?>',
            method: 'post',
            data: zapForm.serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.code === 0) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center});

                    <?php if($node->id){ ?>
                    Zap.reload({callback:function (){
                            console.log(zapload)
                            zapload.dispose();
                            createEditor();
                        }});
                    <?php }else{ ?>
                    location.href = data.redirect_to;
                    <?php } ?>
                } else {
                    ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
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


?>
