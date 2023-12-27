<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:29
 * @lastModified 2023/12/27 上午11:10
 *
 */

namespace zap\cms;

use zap\traits\SingletonTrait;

class AdminPage
{
    use SingletonTrait;

    public static function breadcrumb(): BreadCrumb
    {
        return BreadCrumb::instance();
    }

    public function showFlashMessages(){
        $colorMap = [FLASH_ERROR => 'bgDanger',FLASH_INFO => 'bgInfo',FLASH_SUCCESS=>'bgSuccess',FLASH_WARNING=>'bgWarning'];
        echo '$(function(){';
        foreach ([FLASH_ERROR,FLASH_INFO,FLASH_SUCCESS,FLASH_WARNING] as $flashType){
            if(session()->hasFlash($flashType)){
                $bgColor = $colorMap[$flashType];
                $messages = join("<br/>",session()->getFlash($flashType));
                echo "ZapToast.alert('{$messages}', {bgColor: {$bgColor}, position: Toast_Pos_Center});";

            }
        }
        echo '})';
    }

}