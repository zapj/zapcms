<?php

namespace zapcms\controllers;


use zapcms\controllers\AdminController;
use zap\http\Request;
use zap\http\Response;

class UploadController extends AdminController
{
    /** 允许上传的扩展名白名单（小写） */
    private const ALLOWED_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico',
        'zip', 'rar', '7z', 'tar', 'gz', 'pdf', 'doc', 'docx',
        'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'md', 'csv',
        'mp3', 'mp4', 'avi', 'mov', 'wmv', 'flv', 'webm',
        'json', 'xml',
    ];

    function image()
    {
        if (Request::isPost()) {
            $this->saveUpload('images');
        }
    }


    function file()
    {
        if (Request::isPost()) {
            // fullPath：拖拽上传目录时由前端传入（形如 /dir/sub/file.txt），用于保留目录结构
            $this->saveUpload('', (string)Request::post('path'), (string)Request::post('fullPath', ''));
        }
    }

    /**
     * 统一处理文件上传：校验扩展名白名单、防路径穿越、生成唯一文件名
     * @param string $subDir 子目录（如 images），为空时使用 path 参数
     * @param string $path   文件管理器指定目录
     * @param string $fullPath 目录上传时的相对完整路径（仅用于提取子目录结构）
     */
    private function saveUpload(string $subDir, string $path = '', string $fullPath = '')
    {
        $file = Request::file('file');
        if (!isset($file['error']) || $file['error'] != UPLOAD_ERR_OK) {
            Response::json(['code' => 1, 'msg' => $this->errorMessage($file['error'] ?? UPLOAD_ERR_NO_FILE)]);
            return;
        }

        // 扩展名白名单校验（取原文件名最后一个点之后的部分）
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            Response::json(['code' => 1, 'msg' => '不支持的文件类型: .' . $ext]);
            return;
        }

        // 防路径穿越 + 统一目录分隔
        $path = trim(str_replace(['..', '\\'], '/', (string)$path), '/');
        $relPath = $subDir !== '' ? trim($subDir, '/') : $path;

        // 目录上传：从 fullPath（形如 /dir/sub/file.txt）提取子目录结构并拼接到目标目录
        if ($fullPath !== '') {
            $fullPath = trim(str_replace(['..', '\\'], '/', (string)$fullPath), '/');
            $dirPart = dirname($fullPath);
            if ($dirPart !== '.' && $dirPart !== '/') {
                $relSubDir = trim($dirPart, '/');
                $relPath = $relPath === '' ? $relSubDir : $relPath . '/' . $relSubDir;
            }
        }

        // 系统缩略图缓存目录（thumbs）禁止上传，该目录由系统自动生成，不允许写入
        if ($relPath === 'thumbs' || str_starts_with($relPath, 'thumbs/')) {
            Response::json(['code' => 1, 'msg' => '系统缩略图缓存目录禁止上传文件']);
            return;
        }

        $filename = uniqid() . '.' . $ext;
        $targetDir = $relPath === '' ? storage_path() : storage_path($relPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $targetDir . '/' . $filename)) {
            Response::json(['code' => 1, 'msg' => '文件保存失败']);
            return;
        }

        $urlPath = $relPath === '' ? $filename : $relPath . '/' . $filename;
        Response::json(['code' => 0, 'url' => base_url('/storage/' . $urlPath)]);
    }

    private function errorMessage($code): string
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;
            default:
                $message = "Unknown upload error";
                break;
        }

        return $message;

    }

}