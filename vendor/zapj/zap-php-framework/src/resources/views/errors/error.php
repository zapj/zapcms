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
<?php echo $html;?>
</body>
</html>
