<?php

namespace zapcms\controllers;


use zapcms\controllers\AdminController;
use zapcms\helpers\ThumbHelper;
use zapcms\support\SortingFilesystemIterator;
use zap\helpers\Pagination;
use zap\util\FileUtils;
use zap\util\Str;
use zap\view\View;

class FinderController extends AdminController
{
    function list(){
        // size 格式校验：仅允许 "宽x高" 或 original
        $size = (string)req()->get('size','');
        if(preg_match('/^(\d+)x(\d+)$/', $size, $m)){
            $width = (int)$m[1];
            $height = (int)$m[2];
        }else{
            $width = 136;
            $height = 136;
        }
        $path = trim(req()->get('path',''),'/');
        $path =  str_replace(['..'],'',$path);
        $realPath = realpath(app()->storagePath($path) );
        if(!is_dir($realPath)){
            \response("{$path} 不是目录无法访问")->setStatusCode(403)->send();
        }
        $search = (string)req()->get('search','');
        $data = [];
//        $fsIter = new FilesystemIterator($realPath,FilesystemIterator::KEY_AS_PATHNAME|FilesystemIterator::CURRENT_AS_FILEINFO|FilesystemIterator::SKIP_DOTS);
        $fsIter = new SortingFilesystemIterator($realPath);
        $fsIter->sortByType();
        if($search !== ''){
            $fsIter->match('/' . preg_quote($search, '/') . '/');
        }

        $total = $fsIter->count();

        // 分页 URL：保留 path/search/target 等查询参数，剔除 page 与 initialize。
        // initialize=true 会渲染整个 finder 框架（form+script），若被带入分页链接，
        // 前端 .load() 会把整个框架嵌套进 #finderContent，导致表单嵌套、事件重复绑定。
        $query = req()->get();
        unset($query['page'], $query['initialize']);

        $pageHelper = new Pagination((int)req()->get('page', 1), 10, $query);
        $pageHelper->withPath(url_action('Finder@list') . ($query ? '?' . http_build_query($query) : ''));
        $pageHelper->setTotal($total);
        $pageHelper->setClasses(['nav'=>'pagination pagination-sm justify-content-center mb-0']);
        // 裁剪超出范围的页码，保证 getOffset() 与 currentPage() 一致
        $pageHelper->setCurrentPage($pageHelper->currentPage());
        $fsIter->limit($pageHelper->getOffset(), $pageHelper->getLimit());
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

        // 回调仅允许安全的全局函数名，防止 URL 参数注入到前端 JS
        $callback = (string)req()->get('callback','');
        if(!preg_match('/^[a-zA-Z_$][a-zA-Z0-9_$]*$/', $callback)){
            $callback = '';
        }
        // target 仅允许 #id 或 .class 形式的选择器
        $target = array_values(array_filter(
            array_map('trim', explode('|',(string)req()->get('target',''))),
            fn($t) => preg_match('/^[#.][a-zA-Z0-9_-]+$/', $t) === 1
        ));

        // 缩略图缓存目录（thumbs）禁止上传
        $forbidUpload = $path === 'thumbs' || str_starts_with($path, 'thumbs/');

        View::render('finder.list',[
            'initialize'=>req()->get('initialize',false),
            'path'=>$path,
            'parent_path'=>$parent_path === '.' ? '':$parent_path,
            'type'=>'list',
            'data'=>$data,
            'target'=>$target,
            'callback'=>$callback,
            'size'=>$size,
            'total'=>$total,
            'pagination' => $pageHelper->render(),
            'forbid_upload' => $forbidUpload
        ]);
    }

    public function createDir()
    {
        if(req()->isPost()){
            $dirName = trim((string)req()->post('dir_name'));
            $path = trim((string)req()->post('path'),' \\/');
            $dirName =  str_replace(['..'],'',$dirName);
            $path =  str_replace(['..'],'',$path);

            if($dirName === ''){
                \response(['code'=>1,'msg'=>'目录名称不能为空'])->withJson();
                return;
            }
            // 非法字符校验（Windows 文件系统不允许）
            if(preg_match('/[\/\\\\:*?"<>|]/', $dirName)){
                \response(['code'=>1,'msg'=>'目录名包含非法字符 \\ / : * ? " < > |'])->withJson();
                return;
            }
            // Windows 保留设备名（含 con.txt 这类变体）
            if(preg_match('/^(con|prn|aux|nul|com[1-9]|lpt[1-9])(\..*)?$/i', $dirName)){
                \response(['code'=>1,'msg'=>'该目录名称不可用'])->withJson();
                return;
            }

            $target = storage_path(($path !== '' ? $path . '/' : '') . $dirName);
            if(is_dir($target)){
                \response(['code'=>1,'msg'=>'创建失败，目录已存在'])->withJson();
                return;
            }
            if(mkdir($target,0777,true) === true){
                \response(['code'=>0,'msg'=>'目录创建成功'])->withJson();
                return;
            }
            \response(['code'=>1,'msg'=>'创建失败，请检查目录写入权限'])->withJson();
        }
    }

    public function delete()
    {
        if(req()->isPost()){
            $finderItem = req()->post('finder_item');
            // 未选择时直接提示，避免空提交
            if(empty($finderItem) || !is_array($finderItem)){
                \response(['code'=>1,'msg'=>'请先选择要删除的文件或目录'])->withJson();
                return;
            }
            $path = trim((string)req()->post('path'),' \\/');
            $path =  str_replace(['..'],'',$path);

            $success = 0;
            foreach ($finderItem as $item){
                // 仅保留纯文件名，防止路径穿越删除 storage 之外的文件
                $name = basename(str_replace('\\','/',trim((string)$item,' /')));
                if($name === '' || $name === '.' || $name === '..'){
                    continue;
                }
                $itemPath = storage_path(($path !== '' ? $path . '/' : '') . $name);
                if(is_file($itemPath)){
                    if(FileUtils::delete($itemPath)){
                        $success++;
                    }
                }else if(is_dir($itemPath)){
                    if(FileUtils::deleteDir($itemPath)){
                        $success++;
                    }
                }
            }

            if($success > 0){
                \response(['code'=>0,'msg'=>"成功删除 {$success} 项"])->withJson();
                return;
            }
            \response(['code'=>1,'msg'=>'没有可删除的文件'])->withJson();
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


    private function isImage($ext)
    {
        // 仅包含 Image 库（GD）支持的格式，避免缩略图生成异常
        return in_array(strtolower((string)$ext), ['png','jpg','jpeg','gif','webp'], true);
    }


}