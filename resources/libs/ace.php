<?php
if(is_dir(app()->basePath('/assets/ace'))){
    register_scripts(base_url('/assets/ace/ace.js'));
    register_scripts(base_url('/assets/ace/ext-modelist.js'));

}else{
    register_scripts('https://cdn.staticfile.org/ace/1.29.0/ace.js');
}



