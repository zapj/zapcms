<?php

namespace app\zap\controllers;


use FilesystemIterator;
use zap\AdminController;
use zap\facades\Url;
use zap\http\Request;
use zap\http\Response;
use zap\util\Password;
use zap\view\View;

class FinderController extends AdminController
{

    function list(){
        $path = '/' . trim(req()->get('path','/'),'/');
        $realPath = realpath(app()->storagePath($path) );
//        if(is_file($realPath) && is_readable($realPath)){
//            response(['code'=>0,'msg'=>'','path'=>$path , 'type'=>'content' ,'filename'=>basename($realPath) , 'content'=>file_get_contents($realPath)])->withJson();
//        }
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

//        response(['code'=>0,'msg'=>'','path'=>$path,'type'=>'list','data'=>$data])->withJson();
        View::render('finder.list',['path'=>$path,'type'=>'list','data'=>$data]);
    }



    function faIcons(){
        View::render('finder.faicons');
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