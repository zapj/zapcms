<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS
 */

namespace app\controllers;

use zapcms\services\Sitemap;
use zap\http\Controller;

class SitemapController extends Controller
{
    /**
     * 生成 sitemap XML
     *
     * 路由匹配：
     *   /sitemap.xml          → $type = 'sitemap.xml'       → sitemap 索引
     *   /sitemap-article.xml  → $type = 'sitemap-article.xml' → article 类型 URL set
     *   /sitemap-catalog.xml  → $type = 'sitemap-catalog.xml' → catalog 类型 URL set
     *
     * @param string $type 路由参数（完整文件名）
     */
    public function generate(string $type = 'sitemap.xml')
    {
        // 设置正确的 Content-Type，避免浏览器以 HTML 解析
        header('Content-Type: application/xml; charset=UTF-8');
        header('X-Robots-Tag: noindex');

        $sitemap = new Sitemap();

        // 提取类型名：sitemap[-type].xml
        if (preg_match('/^sitemap(?:-([a-z0-9_-]+))?\.xml$/i', $type, $m)) {
            $typeName = $m[1] ?? '';
        } else {
            $typeName = '';
        }
        
        // 空类型 → 索引
        if ($typeName === '') {
            echo $sitemap->createIndex();
            return;
        }

        // 不支持的类型 → 404
        if (!isset(Sitemap::$types[$typeName])) {
            http_response_code(404);
            return;
        }
       
        echo $sitemap->createUrlSet($typeName);
    }
}
