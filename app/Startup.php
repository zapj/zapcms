<?php
/*
 * Copyright (c) 2023.  ZAP.CN  - ZAP CMS - All Rights Reserved
 * @author Allen
 * @email zapcms@zap.cn
 * @date 2023/12/27 上午11:09
 * @lastModified 2024/08/04
 *
 */

namespace app;

use app\controllers\ZqueryController;
use Twig\Error\Error as TwigError;
use zapcms\models\Node;
use zap\DB;
use zap\exception\NotFoundException;
use zap\exception\ViewNotFoundException;
use zap\http\Router;
use zap\view\View;

class Startup
{
    /** @var string 前端控制器命名空间 */
    protected string $namespace = '\app\controllers';

    public Router $router;
    public string $currentUri;
    public string $baseUrl;

    private string $controller;
    private string $method;
    private string $controllerClass;
    private bool $notFound = false;
    private bool $hasParams;

    public function __construct()
    {
        $this->controller = 'index';
        $this->method = 'index';
    }

    /**
     * 注册前台路由并初始化 CMS 环境
     *
     * @param Router $router 路由器实例
     */
    public function handle(Router $router): void
    {
        $this->router = $router;

        // 计算当前请求 URI（去除查询参数，解码中文等字符）
        $this->currentUri = urldecode(strtok($_SERVER['REQUEST_URI'], '?') ?: '/');
        $this->baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');

        // ──── ZQuery 统一查询 API ────
        $this->router->match(['GET', 'POST'], '/api/zquery', function () {
            (new ZqueryController())->index();
        });
        $this->router->get('/api/zquery/meta', function () {
            (new ZqueryController())->meta();
        });

        // ──── 注册前台兜底路由（匹配所有未被后台匹配的请求）────
        $this->router->any('/{any:.*}', function ($any = '') {
            $this->dispatchFrontend($any);
        });

        // 同时注册根路径
        $this->router->any('/', function () {
            $this->dispatchFrontend('');
        });
    }

    /**
     * 前台请求分发
     */
    protected function dispatchFrontend(string $path): void
    {
        define('IN_ZAPCMS', true);

        // ──── 加载站点配置 ────
        $website = get_options('website', 'REGEXP');
        app()->set('options_website', $website);

        config_set('config.theme', $website['website.theme'] ?? 'basic');
        if (($website['website.theme'] ?? 'basic') !== 'basic') {
            View::paths(themes_path('basic'));
        }

        $theme = $website['website.theme'] ?? 'basic';
        if (is_file(themes_path("{$theme}/functions.php"))) {
            include themes_path("{$theme}/functions.php");
        }

        // ──── 解析 URL ────
        $this->parseUrlPath($path);

        if (!isset($this->controllerClass) || $this->notFound) {
            $this->initRoute();
        }

        if (!isset($this->controllerClass) || !class_exists($this->controllerClass)) {
            $this->router->trigger404();
            return;
        }

        // ──── 调用控制器 ────
        try {
            app()->controller = new $this->controllerClass();
            call_user_func_array([app()->controller, 'setParams'], ['params' => $this->router->params]);

            if (method_exists(app()->controller, '_invoke')) {
                call_user_func_array([app()->controller, '_invoke'], [
                    'method' => $this->method,
                    'params' => $this->router->params
                ]);
            } else {
                if (method_exists(app()->controller, $this->method)) {
                    call_user_func_array([app()->controller, $this->method], $this->router->params);
                } else {
                    throw new NotFoundException('not found');
                }
            }
        } catch (NotFoundException $e) {
            if (method_exists(app()->controller, '_notfound')) {
                call_user_func_array([app()->controller, '_notfound'], [
                    'method' => $this->method,
                    'params' => $this->router->params
                ]);
            } else {
                $this->router->trigger404();
            }
        } catch (ViewNotFoundException $e) {
            echo $e->getMessage();
        } catch (\Error $e) {
            echo $e->getMessage();
        } catch (\Throwable $e) {
            http_response_code(500);
            echo '[' . get_class($e) . '] ' . $e->getMessage();
        }
    }

    /**
     * 从 URL 路径解析控制器和方法
     */
    private function parseUrlPath(string $urlPath): void
    {
        $segments = $urlPath === '' ? [] : array_map('urldecode', preg_split('#/#', trim($urlPath, '/'), -1, PREG_SPLIT_NO_EMPTY));

        if (isset($segments[0]) && preg_match('/^[a-z]+[-_0-9a-z]+$/i', $segments[0])) {
            $controllerClass = $this->namespace . '\\' . Router::convertToName($segments[0]) . 'Controller';
            if (class_exists($controllerClass)) {
                $this->controllerClass = $controllerClass;
                unset($segments[0]);
            } else {
                $this->notFound = true;
            }
            if (!$this->notFound && isset($segments[1]) && preg_match('/^[a-z][a-z0-9-_]+$/i', $segments[1])) {
                $this->method = lcfirst(Router::convertToName($segments[1]));
                unset($segments[1]);
            }
        }

        $this->hasParams = !((count($segments) == 0));
        $this->router->params = array_values($segments);
    }

    /**
     * 初始化 CMS 路由（根据参数确定控制器）
     */
    private function initRoute(): void
    {
        $pageState = pageState();

        if (($segment = current($this->router->params)) !== false) {
            
            // Sitemap 路由匹配（必须在 switch 外部，避免 PHP switch 宽松比较 bug）
            if (preg_match('/^sitemap([A-Za-z0-9_-]+)?.xml$/i', $segment)) {
                $this->resetRoute('Sitemap', 'generate');
                return;
            }

            switch ($segment) {
                case 'tags':
                    $pageState->tags = true;
                    $this->resetRoute('node', 'tags');
                    return;
                case 'tag':
                    $pageState->tag = true;
                    $this->resetRoute('node', 'tag');
                    return;
            }
        }

        $slug = end($this->router->params);
        if ($slug) {
            $node = Node::where('slug', $slug)
                ->where('status', Node::STATUS_PUBLISH)
                ->fetch(FETCH_ASSOC);

            $node or $this->router->trigger404();

            // catalog 类型的内容存在 catalog 表中，需要从 catalog 表补充 content 字段
            if ($node['node_type'] === 'catalog' && empty($node['content'])) {
                $catalog = DB::table('catalog')->where('slug', $slug)->fetch(FETCH_ASSOC);
                if ($catalog && !empty($catalog['content'])) {
                    $node['content'] = $catalog['content'];
                }
            }

            $pageState->node = $node;
            $pageState->nodeId = $node['id'];
            $pageState->nodeType = $node['node_type'];
            $pageState->nodeMimeType = $node['mime_type'];

            if (!$this->resetRoute($node['node_type'], empty($node['mime_type']) ? 'index' : $node['mime_type'])) {
                DB::update('node', ['hits' => DB::raw('hits+1')], ['id' => $node['id']]);
                $this->resetRoute('node', $node['node_type']);
            }
            $pageState->isNode = $node['node_type'] !== 'catalog';
            $pageState->isCatalog = $node['node_type'] === 'catalog';
            return;
        }

        // 首页
        $pageState->isHome = true;
        $this->resetRoute('index', 'index');
    }

    /**
     * 根据控制器名和方法名构建完整的控制器类名
     */
    private function resetRoute(string $controller, string $action): bool
    {
        $this->controller = $controller;
        $this->method = $action;
        $this->controllerClass = $this->namespace . '\\' . Router::convertToName($this->controller) . 'Controller';
        return class_exists($this->controllerClass);
    }
}
