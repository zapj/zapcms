<?php
register_styles(base_url('/assets/plugins/summernote/summernote-lite.min.css'));
register_styles(<<<EOF
.note-editor .note-toolbar .note-color-all .note-dropdown-menu, .note-popover .popover-content .note-color-all .note-dropdown-menu {
    min-width: 343px;
}
.note-toolbar .dropdown-toggle::after {
    content: none;
}
pre {
    display: block;
    padding: 9.5px;
    margin: 0 0 10px;
    font-size: 13px;
    line-height: 1.42857143;
    color: #333;
    word-break: break-all;
    word-wrap: break-word;
    background-color: #f5f5f5;
    border: 1px solid #ccc;
    border-radius: 4px;
}

EOF,ASSETS_HEAD_TEXT);
register_scripts(base_url('/assets/plugins/summernote/summernote-lite.min.js'));
register_scripts(base_url('/assets/plugins/snfinder/summernote-ext-snfinder.js'));
register_scripts(base_url('/assets/plugins/summernote/lang/summernote-zh-CN.js'));
