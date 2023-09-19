<?php

namespace app\zap\controllers;


use zap\AdminController;
use zap\facades\Url;
use zap\http\Request;
use zap\http\Response;
use zap\util\Password;
use zap\view\View;

class UploadController extends AdminController
{

    protected $phpFileUploadErrors = array(
        0 => 'There is no error, the file uploaded with success',
        1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
        2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
        3 => 'The uploaded file was only partially uploaded',
        4 => 'No file was uploaded',
        6 => 'Missing a temporary folder',
        7 => 'Failed to write file to disk.',
        8 => 'A PHP extension stopped the file upload.',
    );

    function image()
    {

        if (Request::isPost()) {
            $file = Request::file('file');
            if (isset($file['error']) && $file['error'] == UPLOAD_ERR_OK) {
                $name = md5(rand(100, 200));
                $ext = explode('.', $_FILES['file']['name']);
                $filename = $name . '.' . $ext[1];
                $destination = storage_path('images/' . $filename);
                $location = $_FILES["file"]["tmp_name"];
                move_uploaded_file($location, $destination);
                Response::json(['code' => 0, 'url' => base_url('/storage/images/' . $filename)]);
            } else {
                Response::json(['code' => 1, 'msg' => $this->codeToMessage($file['error'])]);
            }
        }

    }

    private function codeToMessage($code)
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