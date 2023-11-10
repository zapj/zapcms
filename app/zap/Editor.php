<?php
/*
 * Copyright (c) 2023. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace zap;

use zap\Asset;
use zap\traits\SingletonTrait;

class Editor
{
    use SingletonTrait;

    protected $options = [
        'height'=>400,
        'lang'=>'zh-CN',
        'upload_url'=>'ZAP_UPLOAD_URL',
        'image_upload'=>'function(files){}',
    ];

    protected function init()
    {
        Asset::library('summernote');
        register_scripts(base_url('/assets/plugins/zapuploader.js'));
    }

    public function setOptions($options = []){
        $this->options = array_merge($this->options,$options);
        $this->register();
        return $this;
    }

    public function create($name,$options = []){
        $this->setOptions($options);
        register_scripts(<<<EOF
function createEditor(){
$('{$name}').summernote({
    height: {$this->options['height']},
    lang:'{$this->options['lang']}',
    toolbar:[['snfinder'] ],
    callbacks:{
        onImageUpload: {$this->options['image_upload']}        
    }
});
}
createEditor();
EOF,ASSETS_BODY_TEXT
);
    }

    protected function register(){
        register_scripts(<<<EOF
function zapSendFiles(files) {
    var \$this = $(this);
    for (const i in files) {
         zapUploadFile(files[i],function(url){
            \$this.summernote("insertImage", url);    
        });   
    }
    
}
function zapSendFile(files) {
    var \$this = $(this);   
    console.log(\$this);
    zapUploadFile(files[0],function(url){
        \$this.summernote("insertImage", url);    
    });    
    
    
}
function zapUploadFile(file,callback) {
  data = new FormData();
  data.append("file", file);
  $.ajax({
      data: data,
      type: "POST",
      url: "{$this->options['upload_url']}",
      cache: false,
      contentType: false,
      processData: false,
      dataType:'json',
      success: function(data) {
        if(data.code == 0){
            callback(data.url);
        }else{
            ZapToast.alert(data.msg, {bgColor: bgDanger, position: Toast_Pos_Center});
        }
      }
  });
}
EOF,ASSETS_BODY_TEXT
        );
    }




}