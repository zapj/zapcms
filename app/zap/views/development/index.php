<?php
//\zap\Asset::library('ace');
//register_scripts(base_url('/assets/admin/js/util.js'),ASSETS_BODY);
//register_scripts(base_url('/assets/admin/js/main.js'),ASSETS_BODY);
$this->layout('layouts/common');
?>
<nav class="navbar bg-body-tertiary position-fixed w-100 shadow z-3 zap-top-bar">
    <div class="container-fluid">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);"
             aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item active"><a href="<?php echo url_action('Development') ?>">在线开发工具</a></li>

            </ol>
        </nav>
        <div class=" text-end" >

        </div>
    </div>

</nav>
<main class="container-fluid zap-main">
    <div class="row shadow rounded">
        <div class="col-3 pe-0">
            <ul class="overflow-y-auto cd-accordion cd-accordion--animated margin-top-lg margin-bottom-lg" id="fileTreeView"  style="height: calc(100vh - 270px);">
<!--                <li class="cd-accordion__item cd-accordion__item--has-children">-->
<!--                    <input class="cd-accordion__input" type="checkbox" name ="group-1" id="group-1">-->
<!--                    <label class="cd-accordion__label cd-accordion__label--icon-folder" for="group-1"><span>Group 1</span></label>-->
<!---->
<!--                    <ul class="cd-accordion__sub cd-accordion__sub--l1">-->
<!--                        <li class="cd-accordion__item cd-accordion__item--has-children">-->
<!--                            <input class="cd-accordion__input" type="checkbox" name ="sub-group-1" id="sub-group-1">-->
<!--                            <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-1"><span>Sub Group 1</span></label>-->
<!---->
<!--                            <ul class="cd-accordion__sub cd-accordion__sub--l2">-->
<!--                                <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                                <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                                <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                                <li class="cd-accordion__item">-->
<!---->
<!--                                    <input class="cd-accordion__input" type="checkbox" name ="sub-group-1" id="sub-group-1-1">-->
<!--                                    <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-1-1"><span>Sub Group 1 - 1</span></label>-->
<!---->
<!--                                    <ul class="cd-accordion__sub cd-accordion__sub--l2">-->
<!--                                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                                    </ul>-->
<!--                                </li>-->
<!--                            </ul>-->
<!--                        </li>-->
<!---->
<!--                        <li class="cd-accordion__item cd-accordion__item--has-children">-->
<!--                            <input class="cd-accordion__input" type="checkbox" name ="sub-group-2" id="sub-group-2">-->
<!--                            <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-2"><span>Sub Group 2</span></label>-->
<!---->
<!--                            <ul class="cd-accordion__sub cd-accordion__sub--l2">-->
<!--                                <li class="cd-accordion__item cd-accordion__item--has-children">-->
<!--                                    <input class="cd-accordion__input" type="checkbox" name ="sub-group-level-3" id="sub-group-level-3">-->
<!--                                    <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-level-3"><span>Sub Group Level 3</span></label>-->
<!---->
<!--                                    <ul class="cd-accordion__sub cd-accordion__sub--l3">-->
<!--                                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                                    </ul>-->
<!--                                </li>-->
<!--                                <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                            </ul>-->
<!--                        </li>-->
<!--                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                    </ul>-->
<!--                </li>-->


            </ul>
        </div>
        <div class="col-9 ps-0">
            <div id="editor" class="z-2" style="width: 100%;height: calc(100vh - 270px);">function foo(items) {
    var x = "All this is syntax highlighted";
    return x;
}</div>
        </div>
    </div>

</main>
<script>

    $(function(){
        // var editor = ace.edit("editor");
        // editor.setTheme("ace/theme/monokai");
        // editor.session.setMode("ace/mode/javascript");
        getDir('/');
        const fileTreeView = $('#fileTreeView');
        fileTreeView.on('click','li',function(e){
            e.preventDefault();
            console.log(this)

        });
        // fileTreeView.on('click','a',function(event){
        //     console.log(event.target)
        // });
    })

    function getDir(path){
        $.ajax({
            url:'<?php echo url_action('Development@getDir'); ?>?path='+path,
            success:function(resp){
                const fileTreeView = $('#fileTreeView');
                for (const f in resp.data) {
                    fi = resp.data[f];
                    icon = '<i class="fa fa-folder"></i>';
                    hasSubmenu =  '';

                    if(fi.type === 'dir'){
                        linkTag = `<label class="cd-accordion__label " for="${fi.filename}" > <i class="fa fa-angle-right"></i> ${icon}<span>${fi.filename}</span></label>`;
                        hasSubmenu =  'cd-accordion__item--has-children';
                    }else{
                        icon = '<i class="fa fa-image"></i>';
                        linkTag = `<a href="#0" class="cd-accordion__label" >${icon}<span>${fi.filename}</span></a>`;
                    }
                    fileTreeView.append(`<li class="cd-accordion__item ${hasSubmenu}" data-filename="${fi.filename}" data-path="${resp.path}" data-type="${fi.type}">
                    <input class="cd-accordion__input" type="checkbox" name ="${fi.filename}" id="${fi.filename}">
                    ${linkTag}
                </li>`);
                }
            }
        });
    }
</script>