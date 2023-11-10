<?php

namespace app\zap\controllers;


use FilesystemIterator;
use zap\AdminController;
use zap\facades\Url;
use zap\helpers\Pagination;
use zap\helpers\ThumbHelper;
use zap\http\Request;
use zap\http\Response;
use zap\image\Image;
use zap\Log;
use zap\SortingFilesystemIterator;
use zap\util\FileUtils;
use zap\util\Password;
use zap\util\Str;
use zap\view\View;

class FinderController extends AdminController
{
    function list(){
        list($width,$height) = array_filter(explode('x',req()->get('size')),'intval');
        if(!$width){$width = 136;}
        if(!$height){$height = 136;}
        $path = trim(req()->get('path',''),'/');
        $path =  str_replace(['..'],'',$path);
        $realPath = realpath(app()->storagePath($path) );
        if(!is_dir($realPath)){
            \response("{$path} 不是目录无法访问")->setStatusCode(403)->send();
        }
        $search = req()->get('search','');
        $data = [];
//        $fsIter = new FilesystemIterator($realPath,FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::SKIP_DOTS);
        $fsIter = new SortingFilesystemIterator($realPath);
        $fsIter->sortByType();
        if(!empty($search)){
            $fsIter->match("/$search/");
        }

        $total = $fsIter->count();
        $pageHelper = new Pagination(req()->get('page',1),10,req()->get());
        $pageHelper->url = url_action('Finder@list');
        $pageHelper->setTotal($total);
        $fsIter->limit($pageHelper->getOffset(),$pageHelper->getLimit());
        while($fsIter->valid()){
            $isImage = $this->isImage($fsIter->current()->getExtension());
            $isFile = $fsIter->current()->getType() === 'file';
            $thumbUrl = '';
            if($isFile && $isImage  && !Str::startsWith($path,'thumbs')){
                $thumbUrl = ThumbHelper::thumb("{$path}/{$fsIter->current()->getFilename()}",$width,$height);
            }else if($isFile && $isImage){
                $thumbUrl = base_url("/storage/{$path}/{$fsIter->current()->getFilename()}");
            }
            $data[] = [
                'filename'=>$fsIter->current()->getFilename(),
                'type'=>$fsIter->current()->getType(),
                'ext'=>$fsIter->current()->getExtension(),
                'is_image'=>$isImage,
                'thumb_url'=>$thumbUrl,
                'path'=> $path !== ''  ? "{$path}/{$fsIter->current()->getFilename()}" : $fsIter->current()->getFilename() ,
                'perms'=> substr(sprintf('%o', $fsIter->current()->getPerms()), -4),
                'icon'=> ($fsIter->current()->getType() === 'dir') ? 'fa fa-folder' : $this->getFileIcon($fsIter->current()->getExtension())
            ];
            $fsIter->next();
        }
//        $fileNames = array_column($data,'filename');
//        $fileTypes = array_column($data,'type');
//        array_multisort($fileTypes,SORT_ASC,$fileNames,SORT_ASC ,$data);

        $parent_path = dirname($path);

        View::render('finder.list',[
            'initialize'=>req()->get('initialize',false),
            'path'=>$path,
            'parent_path'=>$parent_path === '.' ? '':$parent_path,
            'type'=>'list',
            'data'=>$data,
            'target'=> array_filter(explode('|',req()->get('target'))),
            'callback'=>req()->get('callback'),
            'size'=>req()->get('size'),
            'total'=>$total,
            'pagination' => $pageHelper->render()
        ]);
    }

    public function createDir()
    {
        if(req()->isPost()){
            $dirName = trim(req()->post('dir_name'),' \\/.');
            $path = trim(req()->post('path'),' \\/.');
            $dirName =  str_replace(['..'],'',$dirName);
            $path =  str_replace(['..'],'',$path);


            $path = storage_path($path .'/'. $dirName);
            if(mkdir($path,0777,true) === true){
                \response()->withJson(['code'=>0,'msg'=>'目录创建成功']);
            }
            if(is_dir($path)){
                \response()->withJson(['code'=>1,'msg'=>'创建失败,目录已存在']);
            }
            \response()->withJson(['code'=>1,'msg'=>'创建失败']);
        }
    }

    public function delete()
    {
        if(req()->isPost()){
            $finder_item = req()->post('finder_item');
            $path = trim(req()->post('path'),' \\/.');
            $path =  str_replace(['..'],'',$path);

            foreach ($finder_item as $item){
                $item_path = storage_path($path .'/'. $item);
                if(is_file($item_path)){
                    FileUtils::delete($item_path);
                }else if(is_dir($item_path)){
                    FileUtils::deleteDir($item_path);
                }

            }

            \response()->withJson(['code'=>0,'msg'=>'删除成功']);
        }
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


    private function isImage($ext): string
    {
        switch ($ext){
            case 'png':
            case 'jpg':
            case 'gif':
            case 'jpeg':
                return  true;
            default:
                return false;
        }
    }


}