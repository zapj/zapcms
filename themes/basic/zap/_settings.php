<?php
defined('IN_ZAP_ADMIN') or die('No permission');
?>
<div class="row mt-3">


    <div class="col-12 col-md-12 mb-2">


        <div class="card">
            <div class="card-header align-items-center d-flex">
                首页幻灯图片
                <div class="ms-auto">
                    <button class="btn btn-success btn-sm" type="button" onclick="saveSettings()">
                        <i class="fa-solid fa-save"></i> 保存
                    </button>
                </div>
            </div>
            <div class="card-body " id="banner_container">
                <?php
                $banner_index = 0;
                $options['basic_home.slide'] = !is_string($options['basic_home.slide']) ?: json_decode($options['basic_home.slide'], true);
                foreach ($options['basic_home.slide'] ?? [] as $i => $item) {
                    $banner_index = $i;

                    ?>
                    <div class="row mt-1 p-2 border ">


                        <div class="col-md-6 col-sm-12 mb-2 justify-content-start align-items-start">
                            <img src="<?php echo $item['img_path'] ?>" class="img-thumbnail rounded "
                                 id="setting_img<?php echo $i; ?>" style="max-height: 150px;" alt=""/>
                        </div>
                        <div class="col-md-6  col-sm-12 mb-2">
                            <label for="setting_input<?php echo $i ?>">图片路径</label>
                            <input type="text" class="form-control form-control-sm mt-1"
                                   name="settings[basic_home.slide][<?php echo $i ?>][img_path]"
                                   id="setting_input<?php echo $i ?>"
                                   value="<?php echo $item['img_path'] ?>"/>
                            <label>链接地址</label>
                            <input type="text" class="form-control form-control-sm mt-1"
                                   name="settings[basic_home.slide][<?php echo $i ?>][link]"
                                   value="<?php echo $item['link'] ?? '#'; ?>"/>
                            <button class="btn btn-danger btn-sm mt-1"
                                    onclick="$(this).closest('div.row').remove()">删除
                            </button>
                        </div>

                    </div>
                    <?php
                } ?>

            </div>
            <div class="card-footer">
                <button class="btn btn-outline-success btn-sm" data-zap-toggle="image" data-zap-callback="add_banner" data-zap-size="original">选择图片
                </button>

            </div>
        </div>


    </div>
    <div class="col-12 col-md-12 mb-2">
        <div class="card">
            <div class="card-header"><?php echo $options['basic_home.service_title']; ?></div>
            <div class="card-body">
                <div class="row mb-3">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">标题</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control form-control-sm" name="settings[basic_home.service_title]" value="<?php echo $options['basic_home.service_title']; ?>"  placeholder="">
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">副标题</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control form-control-sm" name="settings[basic_home.service_subtitle]"  placeholder="" value="<?php echo $options['basic_home.service_subtitle']; ?>">
                    </div>
                </div>
                <?php for ($j = 1; $j < 7; $j++) { ?>


                <div class="row mb-3">
                    <label for="colFormLabelSm" class="col-sm-2 col-form-label col-form-label-sm">服务项目<?php echo $j;?></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control form-control-sm mt-2" name="settings[basic_home.service<?php echo $j;?>_title]"  placeholder="标题"
                                value="<?php echo $options["basic_home.service{$j}_title"]; ?>" />
                        <textarea rows="3" class="form-control form-control-sm mt-2" name="settings[basic_home.service<?php echo $j;?>_content]"  placeholder="内容"
                        ><?php echo $options["basic_home.service{$j}_content"]; ?></textarea>
                        <input type="text" class="form-control form-control-sm mt-2" name="settings[basic_home.service<?php echo $j;?>_icon]"  placeholder="图标"
                               value="<?php echo $options["basic_home.service{$j}_icon"]; ?>" />
                    </div>
                </div>
                <?php } ?>

            </div>
        </div>

    </div>


    <div class="col-12 col-md-12 mb-2">
        <div class="card">
            <div class="card-header">关于我们</div>
            <div class="card-body">
                <textarea id="about-us-editor" name="settings[basic_home.about_us]"><?php echo $options['basic_home.about_us']; ?></textarea>
            </div>
        </div>
    </div>

<?php \zap\Editor::instance()->create('#about-us-editor'); ?>


</div>
<script>
    function PageReload(){
        createEditor();
    }
    banner_index = <?php echo ++$banner_index; ?>;

    function add_banner(event) {
        const original = $(event.target).data('original')
        $('#banner_container').append(`<div class="row mt-1 p-2 border">
                            <div class="col-md-6 col-sm-12 mb-2 justify-content-start align-items-start">
                                    <img src="${original}" class="img-thumbnail rounded " id="setting_img${banner_index}" style="max-height: 150px;" alt=""/>
                            </div>
                            <div class="col-md-6  col-sm-12 mb-2">
                                <label for="setting_input${banner_index}">图片路径</label>
                                <input type="text" class="form-control form-control-sm mt-2" name="settings[basic.slide][${banner_index}][img_path]" id="setting_input${banner_index}" value="${original}" />
                                <button class="btn btn-danger btn-sm mt-1" onclick="$(this).closest('div.row').remove()">删除</button>
                            </div>

                        </div>`);
        banner_index++;

    }


</script>