<?php

namespace app\zap\controllers;

use zap\AdminController;
use zap\Auth;
use zap\DB;
use zap\facades\Url;
use zap\helpers\Pagination;
use zap\http\Request;
use zap\http\Response;
use zap\Log;
use zap\Option;
use zap\Theme;
use zap\User;
use zap\util\Password;
use zap\view\View;

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
            Response::json(['code'=>0,'msg'=>'主题设置成功']);
        }
    }



    public function settings(){
        $theme = Option::get('website.theme','basic');
        $settingFile = themes_path("{$theme}/settings.json");
        if(!is_file($settingFile)){
            Response::redirect(url_action('theme'),'当前主题不支持自定义',FLASH_INFO);
        }
        $customSettings = file_get_contents($settingFile);
        $themeSettings = Option::getKeys($theme,'REGEXP');
        view('theme.settings',[
            'customSettings'=>json_decode($customSettings,true),
            'themeSettings'=>$themeSettings
        ]);
    }

    public function saveSettings()
    {
        if(req()->isPost()){
            $settings = req()->post('settings');
            $theme = Option::get('website.theme','basic');
            $themeSettingsKeys = Option::getKeys($theme,'REGEXP');
            foreach ($settings as $key=>$value){
                if(in_array($key, $themeSettingsKeys)){
                    Option::update($key,$value);
                }else{
                    Option::add($key,$value);
                }

            }

        }

    }

}