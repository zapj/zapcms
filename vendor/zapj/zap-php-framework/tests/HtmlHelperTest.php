<?php
/*
 * Copyright (c) 2025.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2025/4/23 17:51
 * @lastModified 2025/4/23 17:51
 *
 */


use zap\html\Html;

final class HtmlHelperTest extends \PHPUnit\Framework\TestCase {
    public function testHtmlHelper() {

//        $this->expectOutputString(\zap\html\Html::create('a',['id'=>'123'])->setInnerHtml(\zap\html\Html::create('h1')->setInnerHtml('hello')));
//        $this->expectOutputString(Html::a('Hello',['href'=>'https://hello.com','target'=>'_blank']));
//        $this->expectOutputString(Html::b('你好'));
        $this->expectOutputString(Html::form(['action'=>'/html','method'=>'get'])->html(Html::a('hello',['href'=>'/html'])));
    }
}
