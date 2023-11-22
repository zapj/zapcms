<?php
defined('IN_ZAP_ADMIN') or die('No permission');
?>
<div class="row mt-3">
    <div class="col-12 mb-3 border-bottom">
        <h6 class=" pb-2 mb-0">
            <?php echo $settings['title']; ?>

        </h6>

    </div>

    <div class="col-12 col-md-12 mb-2">


        <div class="card">
            <div class="card-header">
                首页幻灯图片
            </div>
            <div class="card-body " id="banner_container">
                <?php
                $banner_index = 0;
                $options['basic.slide'] = !is_string($options['basic.slide']) ?: json_decode($options['basic.slide'], true);
                foreach ($options['basic.slide'] ?? [] as $i => $item) {
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
                                   name="settings[basic.slide][<?php echo $i ?>][img_path]"
                                   id="setting_input<?php echo $i ?>"
                                   value="<?php echo $item['img_path'] ?>"/>
                            <label>链接地址</label>
                            <input type="text" class="form-control form-control-sm mt-1"
                                   name="settings[basic.slide][<?php echo $i ?>][link]"
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
                <button class="btn btn-success" data-zap-toggle="image" data-zap-callback="add_banner" data-zap-size="original">选择图片
                </button>

            </div>
        </div>


    </div>

    <div class=" text-end">
        <button class="btn btn-success btn-sm" type="button" onclick="saveSettings()"><i
                class="fa-solid fa-save"></i> 保存
        </button>

    </div>
</div>
