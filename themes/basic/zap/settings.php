<?php
defined('IN_ZAP_ADMIN') or die('No permission');


use zap\NodeType;

?>


    <nav class="navbar bg-body-tertiary position-fixed w-100 shadow-sm z-3 ">
        <div class="container-fluid">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
                 aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active"><a href="<?php echo url_action('theme') ?>">主题管理</a></li>
                </ol>
            </nav>
            <div class=" text-end">
                <!--            <a href="#" class="btn btn-success btn-sm" ><i class="fa-solid fa-search"></i> 主题市场</a>-->

            </div>
        </div>
    </nav>


        <main class="container zap-main ">
            <ul class="nav ">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="<?php echo url_action('theme@settings')?>">设置</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url_action('theme@settings?page=test')?>">Test</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Link</a>
                </li>

            </ul>
            <form action="" method="post" id="zapForm">
                <?php
                $page = req()->get('page','_settings');
                $view = theme_path('zap/'.$page.'.php');
                if(is_file($view)){
                    include $view;
                }
                ?>


            </form>
        </main>


<script>
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
