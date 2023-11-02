<?php
\zap\Asset::library('ace');
//\zap\Asset::library('codemirror');
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

            </ul>
        </div>
        <div class="col-9 ps-0">
            <ul class="nav nav-tabs" id="fileToolbarTabs" role="tablist">

            </ul>
            <div class="tab-content" id="fileContentTabs">
            </div>

        </div>
    </div>

</main>
<script>
    const fileTreeView = $('#fileTreeView');
    const editorInstances = {};
    $(function(){

        getDir('<?php echo $path;?>',fileTreeView);

        fileTreeView.on('click','li',function(e){
            e.preventDefault();
            e.stopPropagation();
            const path = $(this).data('path');
            const type = $(this).data('type');
            if(!$(this).hasClass('zap-open')){
                if($(this).data('type') === 'file' && editorInstances[path] !== undefined){
                    editorInstances[path].tab.show();
                }else{
                    getDir(path,this)
                }

            }
            if(type==='dir'){
                $(this).toggleClass('zap-open');
            }

        });

    })

    function getDir(path,pEl){
        if(!path){
            path = '/';
        }
        const firstEl = $(pEl).find('i:first');
        const firstElClass = firstEl.attr('class');
        firstEl.attr('class','fa-solid fa-spinner fa-spin pe-1');
        $.ajax({
            url:'<?php echo url_action('Development@getDir'); ?>?path='+path,
            success:function(resp){
                console.log(resp)
                var $pEl = pEl;
                if(resp.type === 'list'){
                    renderFileList(resp.data,$pEl);
                }else if(resp.type === 'content'){
                    openFile(resp.path,resp.filename,resp.content);
                }

            }
        }).done(function(){
            firstEl.attr('class',firstElClass);
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

    function openFile(path,filename,content){
        if(editorInstances[path] !== undefined){
            editorInstances[path].editor.setValue(content, -1);
            editorInstances[path].tab.show();
            return;
        }

        uId = 'ace' + (Math.random()+1).toString(36).substring(10);

        editorInstances[path] = {
            id: uId,
            editor : null,
            tab: null,
            type:'<?php echo option('zap.default_dev_editor','ace'); ?>'
        };
        var defaultEditor = `<textarea id="${uId}" class="z-2" style="height: calc(100vh - 200px);width: 100%"></textarea>`;
        if(editorInstances[path].type === 'ace'){
            defaultEditor = `<div id="${uId}" class="z-2" style="height: calc(100vh - 200px)"></div>`;
            editorInstances[path].type = 'ace';
        }else if(editorInstances[path].type === 'codemirror'){
            editorInstances[path].type = 'codemirror';
        }
        //CodeJar
        $('#fileToolbarTabs').append(`<li class="nav-item" role="presentation">
                    <a class="nav-link" id="${uId}-tab" data-bs-toggle="tab" data-bs-target="#${uId}-tab-pane"
                          title="${path}"   role="tab" aria-controls="home-tab-pane" aria-selected="true">${filename} <i class="ps-2 fa fa-close" onclick="closeTab('${uId}');"></i></a>
                </li>`);

        $('#fileContentTabs').append(`<div class="tab-pane fade" id="${uId}-tab-pane" role="tabpanel" aria-labelledby="${uId}-tab" tabindex="0">${defaultEditor}</div>`);


        if(editorInstances[path].type === 'ace'){
            editorInstances[path].editor = ace.edit(uId);
            const modelist = ace.require("ace/ext/modelist")
            const mode = modelist.getModeForPath(filename).mode
            editorInstances[path].editor.session.setMode(mode)
            editorInstances[path].editor.setValue(content, -1)
        }else if(editorInstances[path].type === 'codemirror'){

        }else {
            document.getElementById(uId).value = content;
        }


        var triggerEl = document.querySelector('#' + uId + '-tab');
        editorInstances[path].tab = new bootstrap.Tab(triggerEl)
        editorInstances[path].tab.show();
    }

    function closeTab(uId){
        delete editorInstances[$('#'+uId+"-tab").attr('title')];
        $('#'+uId+"-tab").parent().remove();
        $('#'+uId+"-tab-pane").remove();

        //select last tab
        lastTabEl = document.querySelector("#fileToolbarTabs li:last-child a");
        if(lastTabEl !== null){
            const lastTab = bootstrap.Tab.getInstance(lastTabEl);
            lastTab.show()
        }

    }
    function getEditor(uId){

    }
</script>