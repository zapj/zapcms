<?php
register_scripts(base_url('/assets/codemirror/lib/codemirror.js'));
register_styles(base_url('/assets/codemirror/lib/codemirror.css'));
$modeUrl = base_url("/assets/codemirror/mode/%N/%N.js");
register_styles(<<<EOF
.CodeMirror {
  width: 100% !important;
 
}
EOF
,ASSETS_HEAD_TEXT);
register_scripts(<<<EOF
CodeMirror.modeURL = '{$modeUrl}';
EOF
,ASSETS_HEAD_TEXT);
register_scripts(base_url('/assets/codemirror/addon/mode/loadmode.js'));


