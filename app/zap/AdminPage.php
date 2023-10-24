<?php
/*
 * Copyright (c) 2023. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace zap;

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