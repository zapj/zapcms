<?php

namespace zap\view;

class Theme
{
    public static function render($template, $data = array(), $output = false) {
        $template = str_replace('.','/',$template);
        $tpl_filenames = [
            'php' => themes_path(config('config.theme','default').'/'.$template.'.php'),
            'twig' => themes_path(config('config.theme','default').'/'.$template.'.twig.php')
        ];
        $templateFile = null;
        $templateEngine = 'php';
        foreach($tpl_filenames as $engine => $filename){
            if(is_file($filename)){
                $templateFile = $filename;
                $templateEngine = $engine;
                break;
            }
        }
        if(is_null($templateFile)){
            die('Template not found');
        }
        return static::engine($templateEngine)->render($templateFile,$data,$output);
    }

    public static function engine($name){
        switch ($name){
            case 'twig':
                return new TwigViewRenderer();
            default:
                return new PHPRenderer();
        }
    }
}