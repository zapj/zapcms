<?php

use zap\cms\Asset;
use zap\facades\Url;

Asset::library('jqueryvalidation');
$this->layout('layouts/common');

$this->view->page_title = '系统设置';
$this->view->page_subtitle = '站点信息 & 第三方代码';
?>

<form id="zapForm">

    <div class="row g-3">
        <div class="col-12">
            <div class="card card-outline card-success">
                <div class="card-header d-flex align-items-center ps-3 pt-0 pb-0 pe-0">
                    <ul class="nav nav-underline me-auto" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                    data-bs-target="#general-tab-pane" type="button" role="tab"
                                    aria-controls="general-tab-pane" aria-selected="true">
                                <i class="fa fa-cog me-1"></i>站点设置
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="third-party-tab" data-bs-toggle="tab"
                                    data-bs-target="#third-party-tab-pane" type="button" role="tab"
                                    aria-controls="third-party-tab-pane" aria-selected="false">
                                <i class="fa fa-code me-1"></i>第三方代码
                            </button>
                        </li>
                    </ul>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool btn-sm" onclick="save()" title="保存">
                            <i class="fa fa-save text-success"></i>
                        </button>
                    </div>
                </div>

                <div class="tab-content card-body p-3">
                    <div class="tab-pane fade show active" id="general-tab-pane" role="tabpanel"
                         aria-labelledby="general-tab" tabindex="0">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label for="website.title" class="form-label small">站点名称 <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="website.title" name="options[website.title]"
                                       placeholder="网站名称" required value="<?php echo $options['website.title'];?>" />
                            </div>
                            <div class="col-md-6">
                                <label for="website.slogan" class="form-label small">网站副标题</label>
                                <input type="text" class="form-control form-control-sm" id="website.slogan" name="options[website.slogan]"
                                       placeholder="网站副标题" value="<?php echo $options['website.slogan'];?>" />
                            </div>
                            <div class="col-12">
                                <label for="website.keywords" class="form-label small">网站关键词</label>
                                <input type="text" class="form-control form-control-sm" id="website.keywords"
                                       placeholder="网站关键词" name="options[website.keywords]"
                                       value="<?php echo $options['website.keywords'];?>" />
                            </div>
                            <div class="col-12">
                                <label for="website.description" class="form-label small">网站简介</label>
                                <input type="text" class="form-control form-control-sm" id="website.description"
                                       placeholder="网站简介 (200字)" name="options[website.description]"
                                       value="<?php echo $options['website.description'];?>" />
                            </div>
                            <div class="col-md-6">
                                <label for="website.icp" class="form-label small">ICP备案信息</label>
                                <input type="text" class="form-control form-control-sm" id="website.icp"
                                       placeholder="ICP备案号" name="options[website.icp]"
                                       value="<?php echo $options['website.icp'];?>" />
                            </div>
                            <div class="col-md-6">
                                <label for="website.copyright" class="form-label small">版权信息</label>
                                <input type="text" class="form-control form-control-sm" id="website.copyright"
                                       placeholder="网站版权信息" name="options[website.copyright]"
                                       value="<?php echo $options['website.copyright'];?>" />
                            </div>
                            <div class="col-md-6">
                                <label for="website.address" class="form-label small">公司地址</label>
                                <input type="text" class="form-control form-control-sm" id="website.address"
                                       placeholder="公司地址" name="options[website.address]"
                                       value="<?php echo $options['website.address'];?>" />
                            </div>
                            <div class="col-md-6">
                                <label for="website.tel" class="form-label small">联系电话</label>
                                <input type="text" class="form-control form-control-sm" id="website.tel"
                                       placeholder="联系电话" name="options[website.tel]"
                                       value="<?php echo $options['website.tel'];?>" />
                            </div>
                            <div class="col-md-6">
                                <label for="website.api_url" class="form-label small">插件市场API地址</label>
                                <input type="text" class="form-control form-control-sm" id="website.api_url"
                                       placeholder="https://api.zap.cn/api/v1" name="options[website.api_url]"
                                       value="<?php echo $options['website.api_url'] ?? '';?>" />
                                <div class="form-text">用于检查更新和获取插件市场数据</div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="third-party-tab-pane" role="tabpanel" aria-labelledby="third-party-tab"
                         tabindex="0">
                        <div class="row g-2">
                            <div class="col-12">
                                <label for="website.head_script" class="form-label small">顶部代码</label>
                                <textarea rows="4" class="form-control form-control-sm" id="website.head_script"
                                          name="options[website.head_script]"><?php echo $options['website.head_script'];?></textarea>
                                <div class="form-text">代码会放在 <?php echo _e('</head>'); ?> 标签之前</div>
                            </div>
                            <div class="col-12">
                                <label for="website.foot_script" class="form-label small">底部代码</label>
                                <textarea rows="4" class="form-control form-control-sm" id="website.foot_script"
                                          name="options[website.foot_script]"><?php echo $options['website.foot_script'];?></textarea>
                                <div class="form-text">代码会放在 <?php echo _e('</body>'); ?> 标签之前</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-center">
                    <button type="button" class="btn btn-success" onclick="save()">
                        <i class="fa fa-save me-1"></i>保存设置
                    </button>
                </div>
            </div>
        </div>
    </div>

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
        const load = Zap.loading('正在保存，请稍后');
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
            load.dispose()
        });
    }
</script>
