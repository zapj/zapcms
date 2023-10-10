<?php
//\zap\Asset::library('ace');
\zap\Asset::library('codemirror');
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
            <ul class="overflow-y-auto cd-accordion cd-accordion--animated margin-top-lg margin-bottom-lg" id="fileTreeView"  style="height: calc(100vh - 160px);">
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
<!--                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                        <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>-->
<!--                    </ul>-->
<!--                </li>-->


            </ul>
        </div>
        <div class="col-9 ps-0">
            <ul class="nav nav-tabs" id="fileToolbarTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane"
                            type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Home <i class="ps-2 fa fa-close" onclick="alert('close');"></i></button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Profile</button>
                </li>


            </ul>
            <div class="tab-content" id="fileContentTabs">
                <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                    <textarea id="editor" class="z-2" >function foo(items) {
    var x = "All this is syntax highlighted";
    return x;
}</textarea>


                </div>
                <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">...</div>
                <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">...</div>
                <div class="tab-pane fade" id="disabled-tab-pane" role="tabpanel" aria-labelledby="disabled-tab" tabindex="0">...</div>
            </div>

        </div>
    </div>

</main>
<script>


    var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
        lineNumbers: true,
        // mode:'javascript'
    });
    CodeMirror.autoLoadMode(editor,'javascript');
    editor.setOption('mode','javascript');
    editor.setSize(100,'calc(100vh - 200px)');

    const fileTreeView = $('#fileTreeView');
    const editorInstances = [];
    $(function(){
        getDir('/',fileTreeView);
        fileTreeView.on('click','li',function(e){
            e.preventDefault();
            e.stopPropagation();
            if($(this).hasClass('zap-open')){
                //close

            }else{
                getDir($(this).data('path'),this)
            }
            $(this).toggleClass('zap-open');

        });

    })

    function getDir(path,pEl){
        if(!path){
            path = '/';
        }
        $(pEl).find('i:first').attr('class','fa-solid fa-spinner fa-spin pe-1');
        $.ajax({
            url:'<?php echo url_action('Development@getDir'); ?>?path='+path,
            success:function(resp){
                // const fileTreeView = $('#fileTreeView');
                $pEl = pEl;
                if(resp.type === 'list'){
                    renderFileList(resp.data,$pEl);
                }else if(resp.type === 'content'){
                    editor.setOption('value',resp.content);
                    openFile('','');
                }

            }
        }).done(function(){
            $(pEl).find('i:first').attr('class','fa-solid fa-angle-right pe-1');
        });
    }

    function renderFileList(data,pEl){
        // console.log($(pEl).get(0).tagName ,'render')
        if($(pEl).get(0).tagName === 'LI'){
            $(pEl).find('ul').remove();

            pEl = $('<ul/>').addClass('cd-accordion__sub cd-accordion__sub--l1').appendTo($(pEl).get(0));
            // sub_ul.addClass('cd-accordion__sub cd-accordion__sub--l1');
            pEl.empty()
        }

        for (const f in data) {
            fi = data[f];
            icon = fi.icon;
            hasSubmenu =  '';

            if(fi.type === 'dir'){
                linkTag = `<label class="cd-accordion__label" for="${fi.filename}" > <i class="fa fa-angle-right pe-1"></i><i class="${icon} pe-1"></i><span>${fi.filename}</span></label>`;
                hasSubmenu =  'cd-accordion__item--has-children zap-tree-has-children';
            }else{
                linkTag = `<label href="#0" class="cd-accordion__label" ><i class="${icon} pe-1"></i><span>${fi.filename}</span></label>`;
            }
            pEl.append(`<li class="cd-accordion__item ${hasSubmenu}" data-filename="${fi.filename}" data-path="${fi.path}" data-type="${fi.type}">
                    ${linkTag}
                </li>`);
        }

    }

    function openFile(filename,content){
        $('#fileToolbarTabs').append(`<li class="nav-item" role="presentation">
                    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane"
                            type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Home <i class="ps-2 fa fa-close" onclick="alert('close');"></i></button>
                </li>`);
    }
</script>