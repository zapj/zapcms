<?php

namespace zap;

use zap\traits\SingletonTrait;
use zap\view\ZView;

class ErrorHandler
{
    use SingletonTrait;

    public static function register(){
        set_error_handler([static::instance(),"errorHandler"]);
        set_exception_handler([static::instance(),'exceptionHandler']);
        register_shutdown_function([static::instance(),'shutdownHandler']);
    }

    function shutdownHandler() {
        if ($error = error_get_last()) {
            if (ob_get_length()) {
                ob_end_clean();
            }
            if(app()->isConsole()){
                Log::error("{$error['message']}",$error);
            }else{
                $html = $this->zapHighlightFile($error['file'], $error['line'], $error['message'], 'Shutdown Error');
                ZView::render(ZAP_SRC . "/resources/views/errors/error.php",['html' => $html,'handler'=>$this]);
            }
            exit;
        }
    }

    function errorHandler($errno, $errstr, $error_file, $error_line) {
        if ($errno == E_USER_ERROR || $errno == E_USER_WARNING) {
            if (ob_get_length()) {
                ob_end_clean();
            }
            $html = $this->zapHighlightFile($error_file, $error_line, $errstr, 'PHP错误 Errno:' . $errno);

            if(config('config.debug',false) === true){
                ZView::render(ZAP_SRC . "/resources/views/errors/error.php",['html' => $html,'handler'=>$this]);
            }else{
                ZView::render(ZAP_SRC . "/resources/views/http/500.html");
            }
            Log::emergency("Error (ErrNo {$errno}) : " . $errstr,['file'=>$error_file, 'line'=>$error_line]);
            exit;
        }
    }

    function exceptionHandler($exception) {
        if (ob_get_length()) {
            ob_end_clean();
        }
        if(config('config.debug',false) === true){
            ZView::render(ZAP_SRC . "/resources/views/errors/exception.php",['exception' => $exception,'handler'=>$this]);
        }else{
            ZView::render(ZAP_SRC . "/resources/views/http/500.html");
        }
        Log::emergency("Exception Type:" . get_class($exception),['message'=>$exception->getMessage(),'file'=>$exception->getFile(), 'line'=>$exception->getLine()]);
        exit;
    }

    function zapHighlightFile($filename, $line_no, $message = '', $title = '错误信息', $offset = 5) {
        // echo $line_no-$offset;
        $start = ($line_no-$offset) > 1  ? $line_no-$offset : 1;

        $end = $line_no+$offset;
        $li_start = $start;

        $code = substr(highlight_file($filename, true), 36, -15);
        //Split lines
        $lines = explode('<br />', $code);

        $lines = array_slice($lines, $start, 10);
        $line_count = count($lines);

        $pad_length = strlen($line_count);

        $return = '';
        if ($message) {
            $return .= "<div style=\"padding: 5px;color: #545454;background-color: #d8d8d8;border: 1px solid #b1b1b1;\">$title</div>";
            $return .= "<div style=\"padding: 5px;color: #545454;border: 1px solid #b1b1b1;\">$message</div><br/>";
        }
        $filename = str_replace(['\\',$_SERVER['DOCUMENT_ROOT'].'/'],['/',''],$filename);
        $return .= "<div style=\"padding: 5px;color: #545454;background-color: #d8d8d8;border: 1px solid #b1b1b1;\">文件名: $filename</div>";
        $return .= '<div style="width: 100%; display: inline-block; display: flex;border: 1px solid #cacaca;"><code style="width: 100%;"><ol start="'.$li_start.'">';

        foreach ($lines as $i => $line) {
            $lineNumber = str_pad($i + 1, $pad_length, '0', STR_PAD_LEFT);
            if ($i % 2 == 0) {
                $numbgcolor = '#C8E1FA';
                $linebgcolor = '#F7F7F7';
                $fontcolor = '#3F85CA';
            } else {
                $numbgcolor = '#DFEFFF';
                $linebgcolor = '#FDFDFD';
                $fontcolor = '#5499DE';
            }


            if ($line == '') $line = '&nbsp;';
            if ($start+1 == $line_no) {
                $linebgcolor = "#fbcbcb";
            }

            $return .= '<li ><div style="background-color: ' . $linebgcolor . '; width: 100%;display: inline-block;">' . $line . '</div></li>';
            $start++;
        }

        $return .= '</ol></code></div>';
        return $return;

    }
}


