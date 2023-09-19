<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title></title>
    <style>
        /*li::before{*/
        /*    background-color: #C8E1FA; white-space: nowrap; width: 23px;   text-align: center; color: #3F85CA;*/
        /*}*/
        /*ol {list-style: none; counter-reset: li}*/

        li::marker {
            /*content: counter(li);*/
            color: #b7b9bb;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
            white-space: nowrap;
            background-color: #f1f1f1;
            text-align: center;
        }

        /*li {counter-increment: li}*/
    </style>
</head>

<body>
<?php
// print_r($exception);
// echo gc_highlight_file($exception->getFile(), $exception->getLine(), $exception->getMessage(), get_class($exception) . ' 异常');

$trace_arr = (array)($exception->getTrace());
//$pdoException = $exception instanceof PDOException;

echo $handler->zapHighlightFile($exception->getFile(), $exception->getLine(), $exception->getMessage(), "错误类型:".get_class($exception));

foreach($trace_arr as $trace){
    if(!isset($trace['file'])) continue;
    if(stripos(str_replace('\\','/',$trace['file']),'zap-php-framework') !== FALSE){
        continue;
    }
    echo $handler->zapHighlightFile($trace['file'],$trace['line'], '', get_class($exception) . ' 异常');
    $msg = '';
}
?>
</body>
</html>
