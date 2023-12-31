<?php

namespace app\zap\controllers;

use app\zap\helpers\ZapFilesystemIterator;
use FilesystemIterator;
use zap\cms\AdminController;
use zap\view\View;

class DevelopmentController extends AdminController
{
    public function index()
    {
        $path = '/' . trim(req()->get('path','/'),'/');
        View::render('development.index',['path'=>$path]);
    }

    public function getDir(){
        $path = '/' . trim(req()->get('path','/'),'/');
        $realPath = realpath(app()->basePath($path) );
        if(is_file($realPath) && is_readable($realPath)){
            response(['code'=>0,'msg'=>'','path'=>$path , 'type'=>'content' ,'filename'=>basename($realPath) , 'content'=>file_get_contents($realPath)])->withJson();
        }
        $data = [];
        $fsIter = new FilesystemIterator($realPath,FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::SKIP_DOTS);
        while($fsIter->valid()){
            $data[] = [
                'filename'=>$fsIter->getFilename(),
                'type'=>$fsIter->getType(),
                'ext'=>$fsIter->getExtension(),
                'path'=> $path !== '/'  ? "{$path}/{$fsIter->getFilename()}" : '/'  . $fsIter->getFilename() ,
                'perms'=> substr(sprintf('%o', $fsIter->getPerms()), -4),
                'icon'=> ($fsIter->getType() === 'dir') ? 'fa fa-folder' : $this->getFileIcon($fsIter->getExtension())
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
        response(['code'=>0,'msg'=>'','path'=>$path,'type'=>'list','data'=>$data])->withJson();
    }

    private function getFileIcon($ext): string
    {
        switch ($ext){
            case 'html':
            case 'htm':
                return 'fa-brands fa-html5';
            case 'css':
                return 'fa-brands fa-css3-alt';
            case 'js':
                return 'fa-brands fa-js';
            case 'php':
                return 'fa-brands fa-php';
            default:
                return 'fa-solid fa-file-code';
        }
    }

}