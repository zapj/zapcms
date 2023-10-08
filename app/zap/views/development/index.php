<?php
\zap\Asset::library('ace');
register_scripts(base_url('/assets/admin/js/util.js'),ASSETS_BODY);
register_scripts(base_url('/assets/admin/js/main.js'),ASSETS_BODY);
$this->layout('layouts/common');
?>

<main class="container">

    <div class="my-3 p-3 bg-body rounded shadow-sm">
        <h6 class="border-bottom pb-2 mb-0"><i class="fa fa-info-circle"></i> 编辑器</h6>

        <ul class="cd-accordion cd-accordion--animated margin-top-lg margin-bottom-lg">
            <li class="cd-accordion__item cd-accordion__item--has-children">
                <input class="cd-accordion__input" type="checkbox" name ="group-1" id="group-1">
                <label class="cd-accordion__label cd-accordion__label--icon-folder" for="group-1"><span>Group 1</span></label>

                <ul class="cd-accordion__sub cd-accordion__sub--l1">
                    <li class="cd-accordion__item cd-accordion__item--has-children">
                        <input class="cd-accordion__input" type="checkbox" name ="sub-group-1" id="sub-group-1">
                        <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-1"><span>Sub Group 1</span></label>

                        <ul class="cd-accordion__sub cd-accordion__sub--l2">
                            <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                            <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                            <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                            <li class="cd-accordion__item">

                                <input class="cd-accordion__input" type="checkbox" name ="sub-group-1" id="sub-group-1-1">
                                <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-1-1"><span>Sub Group 1 - 1</span></label>

                                <ul class="cd-accordion__sub cd-accordion__sub--l2">
                                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>

                    <li class="cd-accordion__item cd-accordion__item--has-children">
                        <input class="cd-accordion__input" type="checkbox" name ="sub-group-2" id="sub-group-2">
                        <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-2"><span>Sub Group 2</span></label>

                        <ul class="cd-accordion__sub cd-accordion__sub--l2">
                            <li class="cd-accordion__item cd-accordion__item--has-children">
                                <input class="cd-accordion__input" type="checkbox" name ="sub-group-level-3" id="sub-group-level-3">
                                <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-level-3"><span>Sub Group Level 3</span></label>

                                <ul class="cd-accordion__sub cd-accordion__sub--l3">
                                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                                </ul>
                            </li>
                            <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                        </ul>
                    </li>
                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                </ul>
            </li>

            <li class="cd-accordion__item cd-accordion__item--has-children">
                <input class="cd-accordion__input" type="checkbox" name ="group-2" id="group-2">
                <label class="cd-accordion__label cd-accordion__label--icon-folder" for="group-2"><span>Group 2</span></label>

                <ul class="cd-accordion__sub cd-accordion__sub--l1">
                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                </ul>
            </li>

            <li class="cd-accordion__item cd-accordion__item--has-children">
                <input class="cd-accordion__input" type="checkbox" name ="group-3" id="group-3">
                <label class="cd-accordion__label cd-accordion__label--icon-folder" for="group-3"><span>Group 3</span></label>

                <ul class="cd-accordion__sub cd-accordion__sub--l1">
                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                </ul>
            </li>

            <li class="cd-accordion__item cd-accordion__item--has-children">
                <input class="cd-accordion__input" type="checkbox" name ="group-4" id="group-4">
                <label class="cd-accordion__label cd-accordion__label--icon-folder" for="group-4"><span>Group 4</span></label>

                <ul class="cd-accordion__sub cd-accordion__sub--l1">
                    <li class="cd-accordion__item cd-accordion__item--has-children">
                        <input class="cd-accordion__input" type="checkbox" name ="sub-group-3" id="sub-group-3">
                        <label class="cd-accordion__label cd-accordion__label--icon-folder" for="sub-group-3"><span>Sub Group 3</span></label>

                        <ul class="cd-accordion__sub cd-accordion__sub--l2">
                            <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                            <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                        </ul>
                    </li>
                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                    <li class="cd-accordion__item"><a class="cd-accordion__label cd-accordion__label--icon-img" href="#0"><span>Image</span></a></li>
                </ul>
            </li>
        </ul>
        <div id="editor" style="height: 300px;width: auto">function foo(items) {
            var x = "All this is syntax highlighted";
            return x;
            }</div>

    </div>
</main>
<script>
    $(function(){

        var editor = ace.edit("editor");
        editor.setTheme("ace/theme/monokai");
        editor.session.setMode("ace/mode/javascript");
    })
</script>