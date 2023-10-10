<?php

namespace app\zap\controllers;

use app\zap\helpers\ZapFilesystemIterator;
use zap\AdminController;
use zap\view\View;

use FilesystemIterator;

class DevelopmentController extends AdminController
{
    public function index()
    {
        View::render('development.index');
    }

    public function getDir(){
        $path = req()->get('path','/');
        $realPath = realpath(app()->basePath($path) );
//        readdir($path);
//        $path = app()->basePath();
        $data = [];
        $fsIter = new FilesystemIterator($realPath,FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::SKIP_DOTS);
        while($fsIter->valid()){
            $data[] = [
                'filename'=>$fsIter->getFilename(),
                'type'=>$fsIter->getType(),
                'ext'=>$fsIter->getExtension(),
                'path'=> "{$path}/{$fsIter->getFilename()}",
                'perms'=> substr(sprintf('%o', $fsIter->getPerms()), -4)
            ];
            $fsIter->next();
        }
        $fileNames = array_column($data,'filename');
        $fileTypes = array_column($data,'type');
        array_multisort($fileTypes,SORT_ASC,$fileNames,SORT_ASC ,$data);

//        foreach ((new ZapFilesystemIterator($path))->sortByType()->limit(0, 100) AS $file)
//        {
//            print $file->getFilename() . "<br>\n";
//            $data[] = [
//                'filename'=>$file->getFilename(),
//                'type'=>$file->getType()
//            ];
//        }
        response(['code'=>0,'msg'=>'','path'=>$path,'data'=>$data])->withJson();
    }

}