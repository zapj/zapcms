<?php if ($initialize) { ?>
<form id="zapFinderForm">
    <div class="row mb-3">
        <div class="col-sm-5">
            <button id="btn-parent" data-bs-toggle="tooltip" title="上一级"
                    class="btn btn-light"><i class="fa-solid fa-level-up-alt"></i></button>

            <button id="btn-refresh" data-bs-toggle="tooltip" title="刷新"
                    class="btn btn-light"><i class="fa-solid fa-rotate"></i></button>
            <button type="button" data-bs-toggle="tooltip" title="上传文件" id="btn-upload" class="btn btn-primary"
                    onclick="document.getElementById('zapUploadFinder').click()">
                <i class="fa-solid fa-upload"></i></button>
            <button type="button" data-bs-toggle="tooltip" title="创建目录" id="btn-folder" class="btn btn-light"><i
                        class="fa-solid fa-folder"></i></button>
            <button type="button" data-bs-toggle="tooltip" title="删除" id="btn-delete" class="btn btn-danger"><i
                        class="fa-regular fa-trash-can"></i></button>

        </div>
        <div class="col-sm-7">
            <div class="input-group">
                <input type="text" name="search" value="" placeholder="Search.." id="input-search" class="form-control">
                <button type="button" id="button-search" data-bs-toggle="tooltip" title="Search"
                        class="btn btn-primary"><i
                            class="fa-solid fa-search"></i></button>
            </div>
        </div>
    </div>
    <div class="row mb-3 finderMkdir " style="display: none">
        <div class="col-sm-12">
            <div class="input-group">
                <div class="input-group">
                    <input type="text" name="create-folder" value="" placeholder="文件夹名称" id="input-folder"
                           class="form-control">
                    <button type="button" title="创建文件夹" id="button-create" class="btn btn-primary"><i
                                class="fa-solid fa-plus-circle"></i></button>
                </div>
            </div>
        </div>
    </div>
    <div class="zap-message mb-2"></div>
    <div id="zapFinderFileList" class="zapUploader">
        <input type="file" id="zapUploadFinder" multiple>
        <div class="progress zap-progress mb-2" style="height: 2px;position: absolute;top: 0;width: 100%;left: 0;">
            <div class="progress-bar zap-progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
                 aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <?php } ?>
        <div id="finderContent">
            <div class="row row-cols-sm-3 row-cols-lg-5 zapFinderFileList">

                <input type="hidden" name="path" value="<?php echo $path; ?>" id="cur-path">
                <input type="hidden" name="parent_path" value="<?php echo $parent_path; ?>" id="parent-path">
                <?php
                foreach ($data as $i => $file) { ?>
                    <div class="col mb-3">
                        <div class="mb-1" style="min-height: 140px;">
                            <a href="<?php echo url_action('finder@list', ['path' => $file['path']]); ?>"
                               class="mb-1 dirList"
                               data-is-image="<?php echo $file['is_image'] ? 'true' : 'false'; ?>"
                               data-type="<?php echo $file['type']; ?>"
                               data-ext="<?php echo $file['ext']; ?>"
                               data-original="<?php echo base_url('/storage/' . $file['path']); ?>"
                            >
                                <?php if ($file['is_image']) { ?>
                                    <img src="<?php echo $file['thumb_url']; ?>" alt="<?php echo $file['filename']; ?>"
                                         title="<?php echo $file['filename']; ?>" class="img-thumbnail">
                                <?php } else { ?>
                                    <i class="<?php echo $file['icon']; ?> fa-9x"></i>
                                <?php } ?>
                            </a>
                        </div>
                        <div class="form-check">
                            <label for="input-path-<?php echo $i; ?>" class="form-check-label"
                                   style="overflow-wrap: anywhere;"><?php echo $file['filename']; ?></label>
                            <input type="checkbox" name="finder_item[]" value="<?php echo $file['filename']; ?>"
                                   id="input-path-<?php echo $i; ?>"
                                   class="form-check-input">
                        </div>
                    </div>
                <?php } ?>
                <br/>

            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-12">
                    <?php echo $pagination; ?>
                </div>
            </div>
        </div>
        <?php if ($initialize) { ?>
    </div>
    <script>
        Zap.EnableToolTip();
        const FinderUrl = '<?php echo url_action('finder@list') ?>?path=';
        const TARGET_LIST = [
            <?php  foreach ($target as $t) {
            echo "'", $t, "',";
        } ?>
        ];
        const zapFinderFileList = $('#finderContent');
        const progressBar = $('.progress-bar');
        function reloadFileList() {
            zapFinderFileList.load(FinderUrl + $('#cur-path').val(), loadCallback);
        }

        $('#btn-refresh').on('click', function (event) {
            event.preventDefault()
            reloadFileList();
        });
        $('#btn-parent').on('click', function (event) {
            event.preventDefault()
            zapFinderFileList.load(FinderUrl + $('#parent-path').val(), loadCallback);
        })

        $('#btn-folder').on('click', function (event) {
            $('.finderMkdir').slideToggle("slow");
        });
        $('#input-folder').on('keydown', function (e) {
            if (e.which === 13) {
                $('#button-create').trigger('click');
            }
        })

        $('#button-create').on('click', function (e) {
            $.ajax({
                url: '<?php echo url_action('Finder@createDir'); ?>',
                method: 'post',
                data: {dir_name: $('#input-folder').val(), path: $('#cur-path').val()},
                success: function (data) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess});
                    if (data.code === 0) {
                        reloadFileList();
                    }
                }
            });
        })

        $('#btn-delete').on('click', function (e) {
            $.ajax({
                url: '<?php echo url_action('Finder@delete'); ?>',
                method: 'post',
                data: $('#zapFinderForm').serialize(),
                dataType: 'json',
                success: function (data) {
                    ZapToast.alert(data.msg, {bgColor: bgSuccess});
                    if (data.code === 0) {
                        zapFinderFileList.load(FinderUrl + $('#cur-path').val(), loadCallback);
                    }
                }
            });
        })
        $('#button-search').on('click',function (){
            zapFinderFileList.load(FinderUrl + $('#cur-path').val() + '&search='+$('#input-search').val(), loadCallback);
        })

        zapFinderFileList.on('click', 'a.dirList', function (event) {
            event.preventDefault()
            event.stopPropagation()
            if ($(this).data('type') === 'dir') {
                zapFinderFileList.load($(this).attr('href'), loadCallback);
            }
            if ($(this).data('type') === 'file') {
                <?php if ($callback) {
                echo $callback, '(', event, ');';
            } ?>
                TARGET_LIST.forEach((value) => {
                    $target = $(value);
                    if ($target[0].nodeName === 'IMG') {
                        $target.prop('src', $(this).find('img').attr('src'))
                    } else if ($target[0].nodeName === 'INPUT') {
                        $target.val($(this).data('original'))
                    }

                })
                window.zapFinder.hide();
            }
        })
        zapFinderFileList.on('click', 'nav a', function (event) {
            event.preventDefault()
            event.stopPropagation()
            zapFinderFileList.load($(this).attr('href'), loadCallback);
        });

        function loadCallback(response, status, xhr) {
            if (status === "error") {
                ZapToast.alert(response, {bgColor: bgDanger})
            }
        }

        var upload = new ZAPUploader('#zapFinderFileList', {
            allowedExtensions: '.jpg|.png|.jpeg',
            url: '<?php echo url_action('Upload@file') ?>',
            customFormData: {
                path: $('#cur-path').val()
            },
            success: function (i, resp) {
                data = JSON.parse(resp);
                if (data.code === 1) {

                }
                console.log(this.progressPercent)
            },
            progress:function(percent){
                progressBar.css('width',percent + '%');
                if(percent === 100){
                    reloadFileList();
                }
            }
        });
    </script>
    <?php } ?>
</form>
