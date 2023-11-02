<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace zap;

use FilesystemIterator;
use zap\traits\SingletonTrait;

class Theme
{

    use SingletonTrait;

    public function getAllThemes(): array
    {
        $themeDir = themes_path();
        $data = [];
        $fsIter = new FilesystemIterator($themeDir,FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::SKIP_DOTS);
        while($fsIter->valid()){
            if($fsIter->getType() !== 'dir') continue;
            $data[] = [
                'dirname'=>$fsIter->getFilename(),
                'perms'=> substr(sprintf('%o', $fsIter->getPerms()), -4),
            ];
            $fsIter->next();
        }
        $fileNames = array_column($data,'dirname');
//        $fileTypes = array_column($data,'type');
        array_multisort($fileNames,SORT_ASC ,$data);
        return $data;
    }

    public function getAllThemesInfo(){
        $themes = $this->getAllThemes();
        $themes_info = [];
        foreach ($themes as $theme){
            $themeInfo = json_decode(file_get_contents(themes_path("{$theme['dirname']}/theme.json")),true) ;
            if(json_last_error() !== JSON_ERROR_NONE){
                continue;
            }
            $themeInfo['dirname'] = $theme['dirname'];
            $themes_info[$theme['dirname']] = $themeInfo;
        }
        return $themes_info;
    }
}