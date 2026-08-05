<?php

namespace app\zap\controllers;

use zap\cms\AdminController;
use zap\cms\IOUtils;
use zap\cms\Option;
use zap\cms\Theme;
use zap\facades\Cache;
use zap\http\Request;
use zap\http\Response;

class ThemeController extends AdminController
{

    public function index(){
        config_set('cache.status','disabled');
        $themes = Theme::instance()->getAllThemesInfo();
        $website_options = Option::getArray('website','REGEXP');
        view('theme.index',['themes'=>$themes,'website_options'=>$website_options]);
    }


    public function activationTheme(){
        if(Request::isPost()){
            $theme = trim(req()->post('theme'));
            if(preg_match('/^[a-z0-9]{1,255}$/i',$theme) === false){
                Response::json(['code'=>1,'msg'=>'主题名字不合法']);
            }
            if(!is_dir(themes_path($theme))){
                Response::json(['code'=>1,'msg'=>'主题不存在']);
            }
            Option::update('website.theme',$theme);
            Theme::instance()->saveThemeOptions($theme);
            Cache::delete('options_website');
            Cache::delete('options_'.$theme);
            Response::json(['code'=>0,'msg'=>'主题设置成功']);
        }
    }



    public function settings(){
        $theme = Option::get('website.theme','basic');
        $themeFile = themes_path("{$theme}/theme.json");
        if(!is_file($themeFile)){
            Response::redirect(url_action('theme'),'当前主题不支持自定义',FLASH_INFO);
        }

        $themeSettings = Option::getArray($theme,'REGEXP');
        $customSettings = IOUtils::readJsonFile($themeFile,true) ?:[];
        $themeSettings = array_merge($customSettings['options'] ?? [],$themeSettings);
        view('theme.settings',[
            'settings'=>$customSettings,
            'options'=>$themeSettings
        ]);
    }

    public function saveSettings()
    {
        if(req()->isPost()){
            $settings = req()->post('settings');
            $theme = Option::get('website.theme','basic');
            $themeSettingsKeys = Option::getKeys($theme,'REGEXP');
            foreach ($settings as $key=>$value){
                if(is_object($value) || is_array($value)){
                    $value = json_encode($value);
                }
                if(in_array($key, $themeSettingsKeys)){
                    Option::update($key,$value);
                }else{
                    Option::add($key,$value);
                }

            }

        }
        \response()->withJson(['code'=>0,'msg'=>'保存成功']);
    }


    private function callCustomPage($method){
        $theme = Option::get('website.theme','basic');
        $themeFile = themes_path("{$theme}/theme.json");
        if(!is_file($themeFile)){
            Response::redirect(url_action('theme'),'当前主题不支持自定义',FLASH_INFO);
        }

        $themeSettings = Option::getArray($theme,'REGEXP');
        $customSettings = IOUtils::readJsonFile($themeFile,true) ?:[];
        $themeSettings = array_merge($customSettings['options'] ?? [],$themeSettings);
        view('theme.custom_page',[
            'page'=>$method,
            'settings'=>$customSettings,
            'options'=>$themeSettings
        ]);
    }

}