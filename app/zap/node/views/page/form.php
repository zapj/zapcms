<?php

use zap\Asset;
use zap\facades\Url;

Asset::library('summernote');
//Asset::library('trumbowyg');
Asset::library('datetimepicker');
Asset::library('jqueryvalidation');
$this->extend('layouts/common');
?>
    <nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 zap-top-bar">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                 aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo Url::action('Node') ?>">内容管理</a></li>
                    <li class="breadcrumb-item" aria-current="page"><a
                                href="<?php echo Url::action("Node@{$_controller}", $_GET) ?>"><?php echo $title; ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo $sub_title; ?></li>
                </ol>
            </nav>
            <div class=" text-end">
                <button type="button" class="btn btn-success btn-sm" onclick="save();"><i class="fa fa-save"></i> 保存</button>
            </div>
        </div>

    </nav>

    <form id="zapForm">
        <input type="hidden" value="<?php echo $node->id; ?>" name="node_id">
        <input type="hidden" name="node[pub_time]" value="<?php echo $node->getPubTimeToDate(); ?>" />
        <input name="node[status]" id="node_status" type="hidden" value="<?php echo $node->status ?: \zap\Node::STATUS_PUBLISH;?>" />
        <input type="hidden" id="node_author_id" name="node[author_id]" value="<?php echo \zap\Auth::user('id') ?>">
        <input type="hidden" name="catalog[]" value="<?php echo $catalogId;?>"  />
        <main class="container-fluid zap-main">
            <div class="row">
                <?php
                $this->include('default/sidebar','leftmenu');
                echo $this->block('leftmenu');
                ?>
                <div class="col-md-9 mb-3">
                    <div class="card  shadow">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="pill" data-bs-target="#zapTabPanel1"
                                       aria-current="true" href="#">常规信息</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-bs-toggle="pill" data-bs-target="#zapTabPanel2">SEO</a>
                                </li>

                            </ul>
                        </div>
                        <div class="tab-content" id="zapTabContent">
                            <div class="card-body tab-pane show active" role="tabpanel" id="zapTabPanel1">
                                <div class="mb-3">
                                    <label for="node_title" class="form-label">标题</label>
                                    <input type="text" class="form-control" name="node[title]" id="node_title"
                                           placeholder="请输入文章标题" required value="<?php echo $node->title; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="node_content" class="form-label">内容</label>
                                    <textarea name="node[content]" id="node_content"
                                              class="form-control summernote"><?php echo $node->content; ?></textarea>
                                </div>




                                <a class="btn btn-link text-black-50 text-decoration-none ps-0"
                                   data-bs-toggle="collapse" href="#morenodesettings" role="button"
                                   aria-expanded="false" aria-controls="morenodesettings">
                                    <i class="fa fa-angle-double-right"></i> 更多设置
                                </a>
                                <div class="collapse" id="morenodesettings">
                                    <div class="mb-3">
                                        <label for="node_hits" class="form-label">文章点击量</label>
                                        <input type="text" class="form-control" name="node[hits]" id="node_hits"
                                               placeholder="文章点击量" value="<?php echo $node->hits ?? 0; ?>"/>
                                    </div>
                                    <div class="mb-3">
                                        <label for="node_sort_order" class="form-label">排序</label>
                                        <input type="text" class="form-control" name="node[sort_order]" id="node_sort_order"
                                               placeholder="排序" value="<?php echo $node->sort_order ?? 0; ?>"/>
                                    </div>
                                </div>


                            </div>
                            <div class="card-body tab-pane" role="tabpanel" id="zapTabPanel2">
                                <div class="mb-3">
                                    <label for="node_keywords" class="form-label">SEO 关键词</label>
                                    <input type="text" class="form-control" name="node[keywords]" id="node_keywords"
                                           placeholder="请输入文章关键词，以逗号分隔"
                                           value="<?php echo $node->keywords; ?>"/>
                                </div>

                                <div class="mb-3">
                                    <label for="node_description" class="form-label">SEO 简介</label>
                                    <textarea type="text" class="form-control" name="node[description]"
                                              id="node_description" rows="4"
                                              placeholder="简介"><?php echo $node->description; ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <?php if($node->add_time){ ?>
                                <div class="mb-3">
                                    <ul class="text-black-50 fw-lighter fs-6">
                                        <li>发布时间: <?php echo date(Z_DATE_TIME,$node->pub_time); ?></li>
                                        <li>更新时间: <?php echo date(Z_DATE_TIME,$node->update_time); ?></li>
                                        <li>创建时间: <?php echo date(Z_DATE_TIME,$node->add_time); ?></li>
                                    </ul>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </form>
    <script>
        $(document).ready(function () {


            //验证
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
            const index = Zap.loadding('正在保存，请稍后', 1);
            $.ajax({
                url: '<?php echo Url::current();?>',
                method: 'post',
                data: zapForm.serialize(),
                dataType: 'json',
                success: function (data) {
                    if (data.code === 0) {
                        ZapToast.alert(data.msg, {
                            bgColor: bgSuccess, position: Toast_Pos_Center, callback: function () {
                                <?php if($node->id){ ?>
                                location.reload();
                                <?php }else{ ?>
                                location.href = data.redirect_to;
                                <?php } ?>

                            }
                        });
                    } else {
                        ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
                    }
                }
            }).always(function () {
                Zap.closeLayer(index)
            });


        }


    </script>
<?php
\zap\Editor::instance()->create('.summernote', [
    'image_upload' => 'zapSendFile',
    'upload_url' => url_action('Upload@image')
]);


?>