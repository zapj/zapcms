<?php

use zap\Asset;
use zap\facades\Url;
Asset::library('summernote');
//Asset::library('trumbowyg');
Asset::library('datetimepicker');
$this->layout('layouts/common');
?>


<main class="container">

    <div class="row p-0">
        <div class="col pt-2 pb-0">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo Url::action('Node') ?>">内容管理</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><a href="<?php echo Url::action('Node@news') ?>">新闻模块</a></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="my-3 p-3 bg-body rounded shadow-sm">
        <div>
            <a class="btn btn-primary btn-sm" href="<?php echo url_action('Node');?>">内容管理</a>
            <a class="btn btn-primary btn-sm" href="<?php echo url_action('Node@news');?>">返回</a>

        </div>
        <hr/>
        <h6 class="border-bottom pb-2 mb-0"><?php echo $title; ?></h6>
        <div>
            <div class="mb-3">
                <label for="content_title" class="form-label">标题</label>
                <input type="text" class="form-control" name="content[title]" id="content_title" placeholder="请输入文章标题">
            </div>
            <div class="mb-3">
                <label for="content_content" class="form-label">内容</label>
                <textarea name="content[content]" id="content_content" class="form-control summernote"></textarea>
            </div>

            <div class="mb-3">
                <label for="content_keywords" class="form-label">SEO 关键词</label>
                <input type="text" class="form-control" name="content[keywords]" id="content_keywords" placeholder="请输入文章关键词，以逗号分隔" />
            </div>

            <div class="mb-3">
                <label for="content_description" class="form-label">SEO 简介</label>
                <input type="text" class="form-control" name="content[description]" id="content_description" placeholder="简介" />
            </div>

            <div class="mb-3">
                <label for="content_pub_time" class="form-label">发布时间</label>
                <input type="text" class="form-control datetimepicker" name="content[pub_time]" id="content_pub_time" value="<?php echo date('Y-m-d H:i:s');?>" />
            </div>

            <div class="text-center" >
                <a class="btn btn-dark" href="<?php echo url_action('Node@news');?>">取消</a>
                <button type="submit" class="btn btn-success">保存</button>

            </div>

        </div>

    </div>


</main>
<script>
    $(document).ready(function() {

        $.datetimepicker.setLocale('zh');
        // $('.summernote').summernote({
        //     height:300,
        //     lang:'zh-CN',
        //     onImageUpload:function () {
        //
        //     }
        // });
        $('.datetimepicker').datetimepicker({
            format:'Y-m-d H:i:s'
        });
    });
</script>
<?php \app\zap\Editor::instance()
    ->create('.summernote',['image_upload'=>'zapSendFile','upload_url'=>url_action('Upload@image')]);
?>