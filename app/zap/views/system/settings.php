<?php
use zap\Asset;
use zap\facades\Url;
Asset::library('jqueryvalidation');
Asset::library('ace');

$this->layout('layouts/common');
?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 zap-top-bar">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item "><a href="<?php echo Url::action('System@settings') ?>">设置</a></li>
                <li class="breadcrumb-item active"><a href="<?php echo Url::action('System@settings') ?>">基础设置</a></li>
            </ol>
        </nav>
        <div class=" text-end" >
            <button type="button" class="btn btn-success btn-sm" onclick="save()"><i class="fa fa-save"></i> 保存</button>
        </div>
    </div>

</nav>
<form id="zapForm">

<!--    <input type="hidden" name="option_autoload[website]" value="1" />-->
    <main class="container zap-main">

        <div class="card shadow">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                data-bs-target="#general-tab-pane" type="button" role="tab" aria-controls="general-tab-pane"
                                aria-selected="true">站点设置
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="third-party-tab" data-bs-toggle="tab"
                                data-bs-target="#third-party-tab-pane" type="button" role="tab"
                                aria-controls="third-party-tab-pane" aria-selected="false">第三方代码
                        </button>
                    </li>

                </ul>
            </div>

            <div class="tab-content card-body" id="myTabContent">
                <div class="tab-pane fade show active" id="general-tab-pane" role="tabpanel"
                     aria-labelledby="general-tab" tabindex="0">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="website.title" class="form-label">站点名称</label>
                            <input type="text" class="form-control" id="website.title" name="options[website.title]" placeholder="网站名称" required
                            value="<?php echo $options['website.title'];?>" />
                        </div>
                        <div class="col-md-6">
                            <label for="website.slogan" class="form-label">网站副标题</label>
                            <input type="text" class="form-control" id="website.slogan" name="options[website.slogan]" placeholder="网站副标题"
                                   value="<?php echo $options['website.slogan'];?>" />
                        </div>
                        <div class="col-12">
                            <label for="website.keywords" class="form-label">网站关键词</label>
                            <input type="text" class="form-control" id="website.keywords" placeholder="网站关键词" name="options[website.keywords]"
                                   value="<?php echo $options['website.keywords'];?>" />
                        </div>
                        <div class="col-12">
                            <label for="website.description" class="form-label">网站简介</label>
                            <input type="text" class="form-control" id="website.description" placeholder="网站简介 (200字)" name="options[website.description]"
                                   value="<?php echo $options['website.description'];?>" />
                        </div>

                        <div class="col-md-6">
                            <label for="website.icp" class="form-label">ICP备案信息</label>
                            <input type="text" class="form-control" id="website.icp" placeholder="ICP备案号" name="options[website.icp]"
                                   value="<?php echo $options['website.icp'];?>" />
                        </div>
<!--                        网站底部信息设置-->

                        <div class="col-md-6">
                            <label for="website.copyright" class="form-label">版权信息</label>
                            <input type="text" class="form-control" id="website.copyright" placeholder="网站版权信息" name="options[website.copyright]"
                                   value="<?php echo $options['website.copyright'];?>" />
                        </div>

                        <div class="col-md-6">
                            <label for="website.address" class="form-label">公司地址</label>
                            <input type="text" class="form-control" id="website.address" placeholder="公司地址" name="options[website.address]"
                                   value="<?php echo $options['website.address'];?>" />
                        </div>

                        <div class="col-md-6">
                            <label for="website.tel" class="form-label">联系电话</label>
                            <input type="text" class="form-control" id="website.tel" placeholder="联系电话" name="options[website.tel]"
                                   value="<?php echo $options['website.tel'];?>" />
                        </div>

                    </div>
                </div>
                <div class="tab-pane fade" id="third-party-tab-pane" role="tabpanel" aria-labelledby="third-party-tab"
                     tabindex="0">
                    <div class="row mb-3">
                        <label for="website.head_script" class="col-sm-2 col-form-label">顶部代码</label>
                        <div class="col-sm-6">
                            <textarea rows="4" class="form-control" id="website.head_script" name="options[website.head_script]"><?php echo $options['website.head_script'];?></textarea>
                        </div>
                        <div class="col-sm-4 text-black-50">
                            代码会放在 <?php echo _e('</head>'); ?> 标签中
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label for="website.foot_script" class="col-sm-2 col-form-label">底部代码</label>
                        <div class="col-sm-6">
                            <textarea rows="4" class="form-control" id="website.foot_script" name="options[website.foot_script]"><?php echo $options['website.foot_script'];?></textarea>
                        </div>
                        <div class="col-sm-4 text-black-50">
                            代码会放在 <?php echo _e('</body>'); ?> 标签中
                        </div>
                    </div>

                </div>

            </div>
            <div class="card-footer text-center">
                <button type="button" class="btn btn-success" onclick="save()">保存</button>
            </div>
        </div>


    </main>
</form>
<script>
    $(function(){
        $('#zapForm').validate({ignore:''});
    })

    function save(){
        const zapForm = $('#zapForm');
        if (!zapForm.valid()) {
            ZapToast.alert('必填项不能为空', {bgColor: bgDanger, position: Toast_Pos_Center});
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
                    ZapToast.alert(data.msg, {bgColor: bgSuccess, position: Toast_Pos_Center});
                } else {
                    ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
                }
            }
        }).always(function () {
            Zap.closeLayer(index)
        });
    }
</script>
