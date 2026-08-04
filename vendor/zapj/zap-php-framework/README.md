# Zap PHP Framework

轻量级 PHP MVC 框架，开箱即用，适合快速开发中小型 Web 应用。

## 环境要求

- PHP >= 7.4
- PDO 扩展（MySQL / PostgreSQL / SQLite）
- OpenSSL 扩展（可选，加密模块需要）
- Redis 扩展或 Predis（可选，Redis 缓存需要）
- Monolog（可选，高级日志功能需要）

## 安装

```bash
composer require zapj/zap-php-framework
```

## 项目目录结构

```
project/
├── app/
│   ├── controllers/       # 控制器
│   ├── models/            # 模型
│   ├── middlewares/       # 中间件
│   └── views/             # 视图模板
├── config/                # 配置文件
│   ├── config.php         # 应用配置
│   ├── database.php       # 数据库配置
│   ├── log.php            # 日志配置
│   ├── cache.php          # 缓存配置
│   └── route.php          # 路由配置
├── public/                # 入口目录
│   └── index.php          # 入口文件
├── var/
│   └── cache/             # 缓存文件
├── vendor/
└── composer.json
```

## 快速开始

### 入口文件 `public/index.php`

```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', true);

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'app');
define('CONFIG_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'config');
define('VAR_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'var');
define('VENDOR_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor');

require_once VENDOR_PATH . '/autoload.php';

(new \zap\App())
    ->environment('development')    // 开发/生产环境
    ->withRoutes()
    ->withView()
    ->withMiddlewares()
    ->run();
```

---

## 路由

### 基本路由

```php
use zap\http\Route;

// HTTP 方法快捷注册
Route::get('/user/{id}', function($id) {
    return "User ID: $id";
});
Route::post('/user', 'UserController@save');
Route::put('/user/{id}', 'UserController@update');
Route::patch('/user/{id}', 'UserController@partialUpdate');
Route::delete('/user/{id}', 'UserController@destroy');

// 占位符语法
Route::get('/posts/{id:\d+}', 'PostController@show');     // 正则约束
Route::get('/posts/{slug}', 'PostController@bySlug');      // 任意字符

Route::any('/contact', 'ContactController@index');          // 匹配所有方法
Route::match(['GET', 'POST'], '/form', 'FormController@handle');
```

### 路由参数约束

```php
// {name:pattern} 格式
Route::get('/user/{id:\d+}', ...);              // 仅数字
Route::get('/user/{name:[a-zA-Z]+}', ...);       // 仅字母
Route::get('/file/{path:.*}', ...);              // 任意路径（包含斜杠）

// 简单占位符 {name} = 匹配除 '/' 外的任意字符
Route::get('/api/{resource}/{id}', ...);
```

### 命名路由 & URL 生成

```php
// 注册命名路由
Route::get('/user/profile', 'UserController@profile')->name('profile');
Route::get('/posts/{id:\d+}', 'PostController@show')->name('post.show');

// 生成 URL
$url = Router::url('profile');                          // /user/profile
$url = Router::url('post.show', ['id' => 123]);         // /posts/123
```

> URL 生成会替换 `{param}` 占位符并自动清理未填充的占位符。

### 路由组

```php
// 前缀分组
Route::group(['prefix' => 'admin'], function() {
    Route::get('/dashboard', 'Admin\DashboardController@index');   // GET /admin/dashboard
    Route::get('/users', 'Admin\UserController@index');            // GET /admin/users
});

// 中间件分组
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function() {
    Route::get('/settings', 'Admin\SettingsController@index');
});

// 嵌套分组
Route::group(['prefix' => 'api'], function() {
    Route::group(['prefix' => 'v1'], function() {
        Route::get('/users', 'Api\V1\UserController@index');       // GET /api/v1/users
    });
});
```

### 资源路由

```php
// 完整 RESTful 路由
Route::resource('posts', 'PostController');
// 自动注册：
//   GET     /posts             → index
//   GET     /posts/create      → create
//   POST    /posts             → save
//   GET     /posts/{id:\d+}    → show
//   GET     /posts/{id:\d+}/edit → edit
//   PUT     /posts/{id:\d+}    → update
//   DELETE  /posts/{id:\d+}    → destroy

// 限定动作
Route::resource('posts', 'PostController', ['only' => ['index', 'show']]);
Route::resource('posts', 'PostController', ['except' => ['destroy']]);
```

### 中间件

```php
// 路由级中间件
Route::get('/profile', 'UserController@profile')->middleware('auth');

// 组级中间件
Route::group(['middleware' => 'auth'], function() {
    Route::get('/dashboard', 'DashboardController@index');
});
```

### 404 处理

```php
// 注册自定义 404 处理器
Router::setNotFound(function($url) {
    return view('errors.404', ['url' => $url], true);
});

// 或指定控制器方法
Router::setNotFound('ErrorController@notFound');
```

### 静态方法速查

| 方法 | 说明 |
|------|------|
| `Route::get($pattern, $handler)` | GET / HEAD |
| `Route::post($pattern, $handler)` | POST |
| `Route::put($pattern, $handler)` | PUT |
| `Route::patch($pattern, $handler)` | PATCH |
| `Route::delete($pattern, $handler)` | DELETE |
| `Route::options($pattern, $handler)` | OPTIONS |
| `Route::any($pattern, $handler)` | 所有方法 |
| `Route::match($methods, $pattern, $handler)` | 指定多个方法 |
| `Route::resource($name, $controller, $opts)` | RESTful 资源 |
| `Route::group($attrs, $callback)` | 路由分组 |
| `Router::url($name, $params)` | 生成命名路由 URL |
| `Router::setNotFound($handler)` | 404 处理器 |

### 路由缓存

编译路由并缓存，跳过每次请求的正则重建开销，显著提升路由分发性能。

#### 自动模式（推荐）

Router 自动读取 `config/cache.php` 中的 `default` 驱动创建缓存，**无需手动配置**：

```php
// config/route.php — 路由文件
use zap\http\Router;

$router = new Router();

// 自动从 config/cache.php 读取驱动并尝试加载缓存
if ($router->loadRoutesFromCache(__FILE__)) {
    return $router;  // ✅ 缓存命中
}

// 注册路由（仅缓存未命中时执行）
Route::get('/users', 'UserController@index');
Route::get('/posts/{id:\d+}', 'PostController@show');
Route::resource('articles', 'ArticleController');
// ...

// 写入缓存（下次请求直接命中）
$router->cacheRoutes(__FILE__);
return $router;
```

#### config/cache.php 配置

```php
return [
    // 切换缓存驱动只需改这里：'file' | 'redis' | 'memcached' | 'memcache'
    'default' => 'redis',

    'file' => [
        'path' => VAR_PATH . '/cache',
    ],

    'redis' => [
        'params' => ['127.0.0.1', 6379],
    ],

    'memcached' => [
        'driver'  => 'memcached',
        'servers' => [['host' => '127.0.0.1', 'port' => 11211]],
    ],

    'status' => 'enabled',
];
```

#### 手动指定驱动

如需覆盖默认配置，可显式传入缓存驱动：

```php
use zap\cache\RedisCache;
use zap\cache\MemcacheCache;

// Redis
Router::setCacheDriver(new RedisCache(['params' => ['10.0.0.5', 6380]]));

// Memcached
Router::setCacheDriver(new MemcacheCache([
    'driver'  => 'memcached',
    'servers' => [['host' => '192.168.1.10', 'port' => 11211]],
]));

// 文件缓存（指定路径）
Router::setCachePath('/data/cache/routes');
```

#### 缓存管理

```php
// 手动清除缓存（部署脚本中常用）
Router::clearRouteCache();

// 查看缓存状态
$info = Router::getCacheInfo();
// [
//     'driver'       => 'redis',
//     'cache_key'    => 'zap.routes.cache',
//     'cached'       => true,
//     'routes_count' => 42,
// ]
```

#### 缓存失效

- 传入的 `__FILE__`（路由文件自身）的 **修改时间** 参与 hash 计算
- 路由文件一旦保存，缓存自动失效，下次请求重新编译
- 多个依赖文件可传入 `$router->cacheRoutes(__FILE__, 'path/to/helpers.php', 'path/to/auth.php')`

#### 限制

闭包路由不可缓存。必须使用 `'Controller@method'` 字符串语法：

```php
// ❌ 不可缓存
Route::get('/api', function() { return json(['ok' => true]); });

// ✅ 可缓存
Route::get('/api', 'ApiController@status');
```

---

## 控制器

```php
namespace App\Controllers;

use zap\http\Controller;

class UserController extends Controller
{
    public function index()
    {
        // 渲染视图
        return view('user.index', ['users' => $users]);
    }

    public function show($id)
    {
        $user = UserModel::findOrFail($id);
        return json($user);
    }
}
```

### RESTful 控制器

```php
use zap\http\RestController;

class PostController extends RestController
{
    public function index()   { /* GET    /posts */ }
    public function create()  { /* GET    /posts/create */ }
    public function save()    { /* POST   /posts */ }
    public function show($id) { /* GET    /posts/{id} */ }
    public function update($id) { /* PUT/PATCH /posts/{id} */ }
    public function destroy($id) { /* DELETE /posts/{id} */ }
}
```

---

## 请求 & 响应

### 获取请求数据

```php
use zap\http\Request;

// 获取输入（自动检测 GET → POST → JSON body 优先级）
$name  = Request::input('name');
$all   = Request::all();             // 所有输入（合并 GET+POST+JSON）
$only  = Request::only(['name', 'email']);
$except = Request::except(['_token', 'password']);

// 检测输入是否存在
if (Request::has('id')) { ... }

// 获取特定来源
$page = Request::query('page', 1);   // 仅 GET
$data = Request::post('title');      // 仅 POST

// JSON 请求体（Content-Type: application/json）
$json = Request::json();             // 返回 array|null
$raw  = Request::rawBody();          // 原始请求体字符串
```

### 请求信息

```php
$method   = Request::method();       // GET/POST/PUT/PATCH/DELETE 等
$url      = Request::url();          // http://domain.com/path
$fullUrl  = Request::fullUrl();      // http://domain.com/path?query=string
$uri      = Request::uri();          // /path
$path     = Request::path();         // /path（不含查询字符串）
$scheme   = Request::scheme();       // http / https
$host     = Request::host();         // domain.com
$port     = Request::port();         // 80 / 443

$ip       = Request::ip();           // 客户端 IP（遍历代理头，过滤非法值）
$ua       = Request::userAgent();    // User-Agent
$referer  = Request::referer();      // HTTP_REFERER
$lang     = Request::language();     // 首选语言（如 zh-CN）

// 方法检测
Request::isGet();        Request::isPost();      Request::isPut();
Request::isPatch();      Request::isDelete();    Request::isOptions();
Request::isHead();       Request::isAjax();      Request::isJson();
```

### 文件上传

```php
$file   = Request::file('avatar');               // 获取单个上传文件
if (Request::hasFile('avatar')) { ... }           // 检查是否有上传
```

### 请求头

```php
$contentType = Request::headers('Content-Type');
$authToken   = Request::headers('Authorization');
$allHeaders  = Request::headers();                // 返回全部请求头
```

### 响应

```php
use zap\http\Response;

// JSON 响应（自动设置 Content-Type）
Response::ok(['data' => $result]);
Response::created(['id' => $newId]);

// 错误响应
Response::notFound('资源不存在');
Response::forbidden('无权限');
Response::unauthorized('请先登录');
Response::badRequest('参数错误');
Response::noContent();

// 自定义响应
$res = new Response();
$res->setContent('Hello World')
    ->setStatusCode(200)
    ->html()          // Content-Type: text/html
    ->header('X-Custom', 'value')
    ->send();

// 响应类型快捷
$res->json()          // Content-Type: application/json
    ->html()          // Content-Type: text/html
    ->text();         // Content-Type: text/plain

// 链式构建 + 手动发送
Response::ok($data)->send();  // 显式调用 send() 发 送
Response::notFound()->send();
```

### 重定向与下载

```php
// 重定向
Response::redirect('/login');
Response::redirect('https://example.com', 301);

// 文件下载
Response::download('/path/to/file.pdf');
Response::download('/path/to/file.pdf', '报告.pdf');

// 设置 Cookie
$res = new Response();
$res->cookie('name', 'value', time() + 3600, '/', '', true, true, 'Lax');
```

### Session 会话

```php
use zap\http\Session;

$session = Session::getInstance();
$session->start();                               // 自动启动（读写时自动调 用）

// 基本操作
$session->set('user', ['id' => 5, 'name' => 'Zap']);
$user = $session->get('user');
$name = $session->get('user.name', 'Guest');    // 点分路径访问嵌套值

// 存在性检查
if ($session->has('user')) { ... }               // 不依赖值真假

// 读取并删除 / 删除
$value = $session->pull('key');                 // 取一次就删
$session->forget('key');
$session->forget(['key1', 'key2']);             // 批量删除

// 数组操作
$session->push('items', 'new item');             // 向数组推入
$session->increment('views');                    // +1
$session->decrement('stock', 2);                 // -2

// 批量操作
$all  = $session->all();                         // 全部数据
$part = $session->only(['user', 'cart']);        // 仅获取指定键

// 安全
$session->regenerate();                          // 防会话固定
$session->regenerate(true);                      // 同时删除旧 session
$session->destroy();                             // 完全销毁
```

### Flash 消息（一次性会话消息）

```php
$session = Session::getInstance();

// 写入 Flash
$session->flash('success', '操作成功');
$session->flash('error', '保存失败，请重试');

// 读取并清除
$messages = $session->getFlash();               // 所有类型
$success  = $session->getFlash('success');       // 指定类型
$alerts   = $session->getFlash(['success', 'error']);

// 检查
if ($session->hasFlash()) { ... }

// 清除
$session->clearFlash('success');                 // 清除指定类型
$session->clearFlash();                          // 清除全部
```

### 请求/响应 静态方法速查

**Request（门面→ZapRequest）：**

| 方法 | 说明 |
|------|------|
| `Request::input($key, $default)` | 通用输入（GET→POST→JSON） |
| `Request::query($key, $default)` | 仅 GET |
| `Request::post($key, $default)` | 仅 POST |
| `Request::json()` | JSON 请求体 |
| `Request::rawBody()` | 原始请求体 |
| `Request::has($key)` | 输入是否存在 |
| `Request::all()` | 所有输入 |
| `Request::only($keys)` | 仅取指定键 |
| `Request::except($keys)` | 排除指定键 |
| `Request::method()` / `isPost()` / ... | 请求方法 |
| `Request::url()` / `fullUrl()` / `path()` | URL 信息 |
| `Request::ip()` / `userAgent()` / `language()` | 客户端信息 |
| `Request::headers($key, $default)` | 请求头 |
| `Request::file($key)` / `hasFile($key)` | 文件上传 |

**Response（静态工厂）：**

| 方法 | 说明 |
|------|------|
| `Response::ok($data)` | 200 JSON |
| `Response::created($data)` | 201 JSON |
| `Response::noContent()` | 204 |
| `Response::notFound($msg)` | 404 JSON |
| `Response::forbidden($msg)` | 403 JSON |
| `Response::unauthorized($msg)` | 401 JSON |
| `Response::badRequest($msg)` | 400 JSON |
| `Response::redirect($url, $code)` | 重定向 |
| `Response::download($path, $name)` | 文件下载 |

---

## 验证器

内置灵活的数据验证组件，支持链式调用、嵌套字段、自定义规则和自定义错误消息。

### 基本使用

```php
use zap\validator\Validator;

// 方式一：静态工厂（默认从 $_GET/$_POST 读取数据）
$v = Validator::make()
    ->rule('required', ['name', 'email'])
    ->rule('email', 'email')
    ->rule('integer', 'age')
    ->rule('between', 'age', [1, 120])
    ->setLabels(['name' => '姓名', 'email' => '邮箱', 'age' => '年龄']);

if ($v->validate()) {
    $data = $v->getValidData();  // 验证通过的数据
} else {
    $errors = $v->firstOfAll();  // 每个字段的第一个错误
}

// 方式二：手动传入数据
$v = Validator::make($_POST)->rule('required', 'username')->validate();
```

### 链式配置 API

```php
$v = Validator::make($data)
    // 添加规则：rule(规则名, 字段, [参数])
    ->rule('required', ['title', 'content'])
    ->rule('max', 'title', 100)
    ->rule('in', 'status', ['draft', 'published', 'archived'])

    // 自定义错误消息
    ->messages([
        'title.required' => '请输入文章标题',
        'title.max'      => '标题不能超过 :param 个字符',
    ])

    // 字段标签（用于错误消息中的 {field} 占位）
    ->setLabels([
        'title'   => '标题',
        'content' => '内容',
        'status'  => '状态',
    ])

    // 字段级遇错停检（title 的第一个规则失败后跳过其余 title 规则）
    ->bail('title')

    // 可空字段（值为空时跳过除 required 以外的规则）
    ->nullable('avatar')

    // 全局遇第一个错误立即停止
    ->stopOnFirstFailure()

    // 验证后回调
    ->after(function($v) {
        if ($v->passes()) {
            // 后置处理，如数据清洗
        }
    })

    // 注册自定义规则命名空间
    ->addNamespace('App\\Validators');
```

### 获取结果

```php
$v = Validator::make($data)->rule('required', 'name');

$v->validate();           // 执行验证，返回 bool
$v->fails();              // 是否失败
$v->passes();             // 是否通过
$v->getValidData();       // 通过验证的数据数组
$v->get('name');          // 获取单个通过验证的值
$v->errors();             // 所有错误 ['field' => ['rule' => 'message']]
$v->firstOfAll();         // 每字段第一个错误 ['field' => 'message']
$v->error('name');        // 指定字段的第一个错误（字符串）
$v->error('name', true);  // 指定字段的所有错误（数组）
$v->validated();          // 通过返回数据，失败抛出 RuntimeException
$v->reset();              // 重置验证器状态
```

### 嵌套字段 & 通配符

```php
// 点号分隔嵌套字段
$data = ['user' => ['name' => 'Zap', 'email' => 'a@b.com']];
$v = Validator::make($data)
    ->rule('required', 'user.name')
    ->rule('email', 'user.email');

// 通配符匹配数组子项
$data = ['items' => [
    ['name' => '商品A', 'price' => 100],
    ['name' => '商品B', 'price' => 200],
]];
$v = Validator::make($data)
    ->rule('required', 'items.*.name')
    ->rule('integer', 'items.*.price');
```

### 可用规则一览

| 规则 | 说明 | 参数示例 |
|------|------|----------|
| `required` | 字段必填 | 无 |
| `required_with` | 指定的其他字段有值时必填 | `['other_field']` |
| `email` | 有效邮箱地址 | 无 |
| `url` | 有效 URL | 无 |
| `domain` | 有效域名 | 无 |
| `ip` | 有效 IP 地址（v4/v6） | 无 |
| `ipv4` | 有效 IPv4 地址 | 无 |
| `ipv6` | 有效 IPv6 地址 | 无 |
| `integer` | 整数 | 无 |
| `double` | 浮点数 | 无 |
| `numeric` | 数字（整数或浮点） | 无 |
| `boolean` | 布尔值（支持 0/1/true/false/yes/no/on/off） | 无 |
| `alpha` | 纯字母 (a-z) | 无 |
| `alpha_num` | 字母 + 数字 | 无 |
| `ascii` | 纯 ASCII 字符 | 无 |
| `min` | 数值最小值 | `10` |
| `max` | 数值最大值 | `100` |
| `between` | 数值范围 | `[1, 120]` |
| `length` | 字符串长度范围 | `[6, 20]` |
| `length_min` | 字符串最小长度 | `6` |
| `length_max` | 字符串最大长度 | `20` |
| `range_length` | 字符串长度范围（Length 别名） | `[6, 20]` |
| `in` | 值必须在列表中 | `['admin', 'editor']` |
| `not_in` | 值不能在列表中 | `['root', 'super']` |
| `regex` | 正则匹配 | `'/^\d{6}$/'` |
| `date` | 有效日期（默认 Y-m-d） | `'Y-m-d H:i:s'` |
| `date_format` | 日期格式（Date 别名） | `'d/m/Y'` |
| `json` | 合法 JSON 字符串 | `'array'` / `'object'` |
| `confirmed` | 字段与 `{field}_confirmation` 一致 | 无 |
| `same` | 与指定字段值相同 | `'email'` |
| `different` | 与指定字段值不同 | `'old_password'` |
| `distinct` | 数组值唯一无重复 | 无 |
| `is_array` | 必须为数组 | 无 |
| `callback` | 自定义验证函数或类 | `function($name, $value) { ... }` |

### Callback 规则

```php
// 闭包方式
$v = Validator::make($data)
    ->rule('callback', 'custom_field', function($name, $value) {
        return $value === 'expected_value';
    });

// 类方式（需实现 check($name, $value) 方法）
$v = Validator::make($data)
    ->rule('callback', 'custom_field', MyValidationRule::class);
```

### 扩展自定义规则

```php
// 1. 创建规则类
namespace App\Validators;

use zap\validator\AbstractRule;

class Mobile extends AbstractRule
{
    public function validate($name, $value)
    {
        return preg_match('/^1[3-9]\d{9}$/', $value) === 1;
    }

    public function translateMsgKey()
    {
        return 'rule_mobile';  // 语言文件中的 key
    }
}

// 2. 运行时注册命名空间
$v = Validator::make($data)
    ->addNamespace('App\\Validators')
    ->rule('mobile', 'phone');
```

> 框架查找规则时，先搜索内置的 `zap\validator\rules`，再搜索通过 `addNamespace()` 注册的自定义命名空间。

---

## 国际化 (i18n)

框架内置灵活的国际化组件，支持多语言文件加载、参数替换、回退语言和复数翻译。

### 基本使用

```php
use zap\i18n\Language;

// 设置当前语言
Language::locale('zh_CN');

// 获取翻译
echo __('messages.welcome', ['name' => 'Zap']);    // "欢迎 Zap"
echo trans('messages.welcome', ['name' => 'Zap']);  // 同上
echo __('messages.title');                            // 无参数
echo __('messages.greeting', 'Zap');                  // 旧式 {value} 替换

// 复数翻译
echo trans_choice('messages.apples', 1);    // "1 个苹果"
echo trans_choice('messages.apples', 5);    // "5 个苹果"
```

### 语言文件

框架支持 PHP 和 JSON 两种格式，存放在 `resources/languages/{locale}/` 目录下：

**PHP 格式** — `resources/languages/zh_CN/messages.php`：
```php
<?php
return [
    'welcome' => '欢迎 {name}',
    'title'   => '我的网站',
    'apples'  => [
        'one'   => '{count} 个苹果',
        'other' => '{count} 个苹果',
    ],
];
```

**JSON 格式** — `resources/languages/en/messages.json`：
```json
{
    "welcome": "Welcome {name}",
    "title": "My Website",
    "apples": {
        "one": "{count} apple",
        "other": "{count} apples"
    }
}
```

### 配置方法

```php
use zap\i18n\Language;

// 获取 / 设置当前语言
$locale = Language::locale();           // 'zh_CN'
Language::locale('en');                 // 切换到英文

// 设置回退语言（当前语言找不到翻译时自动尝试）
Language::fallback('en');

// 添加自定义语言文件路径
Language::addPath('/my-app/resources/languages');

// 获取所有可用语言
$locales = Language::availableLocales();  // ['zh_CN', 'en']

// 获取所有搜索路径
$paths = Language::getPaths();
```

### 参数替换

支持多种参数格式，通过 `{key}` 占位符替换：

```php
// 数组参数：{key} → value
__('messages.greeting', ['name' => 'Zap', 'role' => '管理员']);
// "你好 Zap，你的角色是 管理员"

// 字符串参数（旧式，仅替换 {value}）
__('messages.input', 'hello');
// 消息中 {value} 被替换为 hello
```

### 复数翻译

语言文件中定义 `.one` / `.other` 后缀区分单复数：

```php
// messages.php
return [
    'comment' => [
        'one'   => '{count} 条评论',
        'other' => '{count} 条评论',
    ],
];

// 使用
echo trans_choice('messages.comment', 1);  // "1 条评论"
echo trans_choice('messages.comment', 8);  // "8 条评论"
```

> 中文通常不需要区分单复数，但框架保留此能力以支持英文等多语言场景。

### 动态消息注册

```php
// 运行时添加翻译（不依赖文件）
Language::with(['app.name' => '我的应用', 'app.version' => '1.0']);
// 或
Language::set('app.author', 'Zap Team');

// 获取
__('app.name');  // "我的应用"

// 检查是否存在
Language::has('app.name');  // true
```

### Helper 函数速查

| 函数 | 说明 |
|------|------|
| `__($key, $params)` | 推荐用法：获取翻译（参数数组） |
| `trans($key, $params, $value)` | 翻译（支持旧式 {value} 替换） |
| `trans_choice($key, $count, $params)` | 复数翻译 |

---

## HTTP 网络请求

框架提供 `Requests`（全功能）和 `Curl`（精简兼容）两种 HTTP 客户端，后者内部复用前者。

### 基本请求

```php
use zap\net\Requests;

// GET 请求
$response = Requests::get('https://api.example.com/users', ['page' => 1]);
echo $response->body();       // 响应体
echo $response->status();     // 200

// POST 表单
$response = Requests::post('https://api.example.com/login', [
    'username' => 'admin',
    'password' => 'secret',
]);

// PUT / PATCH / DELETE
$response = Requests::put('https://api.example.com/users/1', ['name' => '新名称']);
$response = Requests::delete('https://api.example.com/users/1');
```

### Response 对象

所有请求方法返回 `zap\net\Response` 对象：

```php
$r = Requests::get('https://api.example.com/data');

$r->body();              // string — 响应体
(string) $r;             // 同 body()，可当字符串用
$r->json();              // array — JSON 解析
$r->json(false);         // object — JSON 解析为对象
$r->status();            // int — HTTP 状态码 (200, 404, …)
$r->ok();                // bool — 2xx 检查
$r->clientError();       // bool — 4xx 检查
$r->serverError();       // bool — 5xx 检查
$r->contentType();       // string — Content-Type
$r->totalTime();         // float — 请求耗时（秒）
$r->effectiveUrl();      // string — 最终 URL（跟随重定向后）
$r->info();              // array — curl_getinfo 完整信息
```

### JSON 请求

```php
// 发送 JSON
$r = Requests::json('POST', 'https://api.example.com/data', [
    'title' => 'Hello',
    'body'  => 'Content',
]);

// 快捷方法
$r = Requests::postJson('https://api.example.com/data', ['key' => 'value']);
$data = Requests::getJson('https://api.example.com/data', ['page' => 1]);
```

### 自定义请求头

```php
$r = Requests::post('https://api.example.com/data', $data, [
    'Authorization: Bearer token123',
    'X-Custom-Header: value',
    'Accept: application/json',
]);
```

### 请求选项

```php
$r = Requests::post('https://api.example.com/data', $data, $headers, [
    'timeout'          => 30,    // 超时（秒）
    'connect_timeout'  => 5,     // 连接超时（秒）
    'ssl_verify'       => false, // 跳过 SSL 验证（不推荐）
    'follow_redirects' => true,  // 跟随重定向
    'max_redirects'    => 5,     // 最大重定向次数
    'cookie'           => 'key=value',           // Cookie 字符串
    'cookie_file'      => '/tmp/cookies.txt',    // Cookie 文件路径
    'auth'             => ['username', 'pass'],  // HTTP Basic Auth
    'referer'          => 'https://example.com', // Referer
]);
```

### 文件上传

```php
$r = Requests::multipart('https://api.example.com/upload', [
    'title' => '我的图片',         // 普通字段
], [
    'image' => '/path/to/photo.jpg',  // 文件字段
]);
```

### 并发请求

```php
$responses = Requests::multi([
    ['method' => 'GET',  'url' => 'https://api.example.com/users'],
    ['method' => 'GET',  'url' => 'https://api.example.com/posts'],
    ['method' => 'POST', 'url' => 'https://api.example.com/log', 'params' => ['event' => 'visit']],
]);

foreach ($responses as $r) {
    echo $r->status() . ': ' . $r->body();
}
```

### 全局配置

```php
Requests::setUserAgent('MyApp/1.0');
Requests::setDefaultTimeout(60);
Requests::setDefaultConnectTimeout(15);
Requests::setCaCert('/custom/cacert.pem');
```

### Curl 精简版（向后兼容，返回字符串）

```php
use zap\net\Curl;

$html = Curl::get('https://example.com/page', ['id' => 1]);
$result = Curl::post('https://api.example.com/login', ['user' => 'admin', 'pass' => '123']);
```

### 错误处理

```php
use zap\exception\CurlException;

try {
    $r = Requests::get('https://down.example.com/api');
} catch (CurlException $e) {
    echo '错误码: ' . $e->getCode();
    echo '错误信息: ' . $e->getMessage();
}
```

---

## 助手 (Helpers)

框架内置分页和 URL 生成助手，可在控制器和视图中直接使用。

### 分页器 (Pagination)

面向 API 和内页渲染的分页组件，支持数组元数据输出和 HTML 分页条生成。

#### 基本使用

```php
use zap\helpers\Pagination;

// 创建分页实例（总数，每页条数，当前页）
$p = new Pagination(200, 15, 1);

// 静态工厂
$p = Pagination::make(200, 15, 1);
```

#### 读取分页信息

```php
$p->currentPage();     // 当前页码
$p->perPage();         // 每页条数
$p->total();           // 总记录数
$p->totalPages();      // 总页数
$p->hasPages();        // 是否有多页
$p->hasMorePages();    // 是否还有下一页
$p->isFirstPage();     // 是否第一页
$p->isLastPage();      // 是否最后一页
$p->firstItem();       // 当前页第一条记录序号
$p->lastItem();        // 当前页最后一条记录序号
$p->offset();          // 数据库查询 offset
```

#### URL 生成

```php
// 设置基础路径
$p->withPath('/posts');

$p->url(3);              // /posts/3
$p->previousPageUrl();   // /posts/2
$p->nextPageUrl();       // /posts/4
$p->firstPageUrl();      // /posts/1
$p->lastPageUrl();       // /posts/14
```

#### API / JSON 元数据

```php
// 数组格式（for API response）
$meta = $p->meta();
// [
//     'current_page'   => 1,
//     'per_page'       => 15,
//     'total'          => 200,
//     'total_pages'    => 14,
//     'from'           => 1,
//     'to'             => 15,
//     'has_more'       => true,
//     'next_page_url'  => '/posts/2',
//     'prev_page_url'  => '#',
//     'first_page_url' => '/posts/1',
//     'last_page_url'  => '/posts/14',
// ]

$json = $p->toJson();    // JSON 字符串
```

#### HTML 渲染

```php
// Bootstrap 5 风格（默认）
echo $p->render();

// 自定义前后标签
echo $p->render('上一页', '下一页');

// Bootstrap 4 兼容
echo $p->renderBootstrap4();

// 简易模式（仅上一页/下一页）
echo $p->renderSimple();

// 记录数统计
echo $p->renderCount();  // 共 200 条，每页 15 条，当前第 1 / 14 页
```

#### 链式配置

```php
$p = Pagination::make(200)
    ->withPath('/articles')
    ->setPerPage(20)
    ->setCurrentPage(3)
    ->onEachSide(2)              // 当前页左右各显示 2 个页码
    ->setClasses([               // 自定义 CSS 类名
        'nav'      => 'my-pagination',
        'active'   => 'is-active',
        'disabled' => 'is-disabled',
    ]);

echo $p->render();
```

#### 自定义渲染

```php
$p->withRender(function($html, $pagination) {
    return '<div class="custom-pager">' . $html . '</div>';
});
```

### URL 助手 (UrlHelper)

生成应用 URL，支持命名路由、控制器动作和安全连接。

#### 基本使用

```php
use zap\facades\Url;

// 基础 URL
echo Url::base();        // http://localhost
echo Url::base('https://example.com');  // 设置基础 URL

// 首页
echo Url::home();        // http://localhost

// 完整 URL
echo Url::full();        // http://localhost/users?id=1

// 上一页（Referer）
echo Url::previous();    // 上一页 URL，无 Referer 时回退到首页

// 当前路径
echo Url::current();     // /users
```

#### 命名路由

```php
// 定义路由
Route::get('/user/{id}', 'UserController@show')->name('profile');

// 生成 URL
echo Url::route('profile', ['id' => 1]);            // /user/1
echo Url::route('profile', ['id' => 2, 'tab' => 'posts']);  // /user/2?tab=posts

// 绝对 URL
echo Url::route('profile', ['id' => 1], true);      // http://localhost/user/1
```

#### 控制器动作

```php
echo Url::action('UserController@list');                    // /UserController@list
echo Url::action('UserController@show', [], ['id' => 5]);   // /UserController@show?id=5
```

#### URL 格式化

```php
// 替换占位符
echo Url::to('/user/{id}/edit', ['id' => 10]);       // /user/10/edit

// 未匹配参数追加为查询串
echo Url::to('/search', ['q' => 'php', 'page' => 2]); // /search?q=php&page=2

// 未匹配参数作为路径段
echo Url::to('/search', ['q' => 'php', 'page' => 2], false); // /search/php/2
```

#### 当前路由信息

```php
Url::controller();      // 当前控制器名
Url::method();          // 当前方法名
Url::getRouteData();    // 完整路由数据数组
Url::getRouteData('profile');  // 指定命名路由的数据
```

#### 激活状态检测

```php
// 布尔检查
if (Url::isActive('/admin*')) { /* 当前在后台 */ }

// 返回 class 名
<a class="<?= Url::isActive('/dashboard', 'nav-active') ?>">首页</a>

// 兼容旧版（直接输出）
Url::active('/admin*', 'class="active"');
```

#### 安全连接

```php
echo Url::secure('/admin/login');    // https://localhost/admin/login
```

---

## 数据库

### 配置

```php
// .env
DB_DRIVER=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test
DB_USERNAME=root
DB_PASSWORD=
DB_PREFIX=zap_
DB_CHARSET=utf8mb4
```

```php
// config/database.php
return [
    'default' => 'mysql',
    'mysql' => [
        'driver'   => env('DB_DRIVER', 'mysql'),
        'host'     => env('DB_HOST', '127.0.0.1'),
        'port'     => env('DB_PORT', '3306'),
        'dbname'   => env('DB_DATABASE', 'test'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset'  => env('DB_CHARSET', 'utf8mb4'),
        'prefix'   => env('DB_PREFIX', 'zap_'),
    ],
    // PostgreSQL 同样支持
];
```

### Query Builder（全参数化查询，防 SQL 注入）

#### 基础 CRUD

```php
use zap\DB;

// SELECT
$users = DB::table('users')
    ->select('id', 'name', 'email')
    ->where('status', 1)
    ->orderBy('id', 'desc')
    ->limit(10)
    ->get();

// 单行查询
$user = DB::table('users')->where('id', 1)->first();
$user = DB::table('users')->find(1);
$name = DB::table('users')->where('id', 1)->value('name');  // 单个字段值

// 列提取
$ids = DB::table('users')->pluck('id');                    // [1, 2, 3, ...]
$map = DB::table('users')->pluck('name', 'id');            // [1=>'张三', 2=>'李四']

// INSERT
$id = DB::table('users')->insert(['name' => '张三', 'email' => 'zs@example.com']);

// INSERT IGNORE
DB::table('users')->insertOrIgnore(['email' => 'zs@example.com']);

// UPDATE
DB::table('users')->where('id', 1)->update(['name' => '张三（改名）']);

// increment / decrement
DB::table('posts')->where('id', 1)->increment('views');
DB::table('posts')->where('id', 1)->increment('views', 5, ['updated_by' => 1]);
DB::table('stock')->where('id', 1)->decrement('quantity', 1);

// DELETE
DB::table('users')->where('id', 1)->delete();
```

#### DB Facade CRUD（表名+数据，自动参数化绑定）

```php
// INSERT
$id = DB::insert('users', ['name' => '张三', 'email' => 'zs@example.com']);

// UPDATE
$rows = DB::update('users', ['name' => '张三（改名）'], ['id' => 1]);

// DELETE
$rows = DB::delete('users', ['id' => 1]);
$rows = DB::delete('users', 'status=0 AND created_at<?', ['2023-01-01']);

// 批量插入
DB::batchInsert('users', [
    ['name' => 'A', 'email' => 'a@a.com'],
    ['name' => 'B', 'email' => 'b@b.com'],
]);

// Upsert / Replace
DB::upsert('users', ['id' => 1, 'name' => '新名字']);
DB::replace('users', ['id' => 1, 'name' => '替换']);

// 计数 & 键值对
$cnt = DB::count('users', 'status=?', [1]);
$map = DB::keyPair('settings', 'key, value');
```

#### WHERE 条件

```php
// 基础条件
->where('status', 'published')              // status = 'published'
->where('age', '>', 18)                     // age > 18
->where('age', '>=', 18)                    // age >= 18
->where('name', 'LIKE', '%张%')              // name LIKE '%张%'

// OR 条件
->where('status', 1)->orWhere('role', 'admin')

// IN / NOT IN
->whereIn('category_id', [1, 2, 3])
->whereNotIn('category_id', [4, 5])

// BETWEEN
->whereBetween('created_at', ['2024-01-01', '2024-12-31'])
->whereNotBetween('price', [100, 500])

// NULL
->whereNull('deleted_at')
->whereNotNull('email_verified_at')
->orWhereNull('parent_id')

// 列与列比较
->whereColumn('updated_at', '>', 'created_at')
->orWhereColumn('a', 'b')

// 子查询 / 嵌套条件
->where(function ($query) {
    $query->where('status', 'active')
          ->orWhere('role', 'admin');
})

// 原始表达式（慎用，避免用户输入）
use zap\db\Expr;
->where('views', '>', new Expr('likes * 2'))
```

#### 聚合函数

```php
$count  = DB::table('users')->count();
$max    = DB::table('orders')->max('amount');
$min    = DB::table('orders')->min('amount');
$sum    = DB::table('orders')->sum('amount');
$avg    = DB::table('orders')->avg('amount');
$exists = DB::table('users')->where('email', 'test@example.com')->exists();
```

#### JOIN

```php
DB::table('posts')
    ->join('users', 'u', 'posts.user_id = u.id')       // INNER JOIN
    ->leftJoin('categories', 'c', 'posts.category_id = c.id')
    ->rightJoin('tags', 't', 'posts.tag_id = t.id')
    ->select('posts.*', 'u.name as author')
    ->get();
```

#### GROUP BY / HAVING / DISTINCT

```php
DB::table('orders')
    ->select('user_id', DB::raw('SUM(amount) as total'))
    ->groupBy('user_id')
    ->having('total', '>', 100)
    ->get();

DB::table('users')->distinct()->pluck('role');
```

#### 排序

```php
->orderBy('created_at', 'desc')
->orderBy('id', 'asc')
->orderByDesc('created_at')
->latest('updated_at')
->oldest('id')
->inRandomOrder()            // RAND()
->reorder('priority', 'desc') // 清除已有 ORDER BY 后重排
```

#### 分页

```php
// 返回 Pagination 实例（含元数据和 HTML 渲染）
$pager = DB::table('posts')->paginate(15);
echo $pager->render();

// 手动 offset / limit
DB::table('posts')->limit(15)->offset(30)->get();
```

#### 大数据集

```php
// 分块处理，避免内存溢出
DB::table('logs')->chunk(500, function ($rows, $page) {
    foreach ($rows as $row) {
        // 处理每一行...
    }
});

// 逐行处理
DB::table('users')->each(function ($user, $index) {
    echo $user->name;
});
```

#### UNION

```php
$first  = DB::table('articles')->where('status', 'published');
$second = DB::table('archives')->where('status', 'published');
$results = $first->union($second)->get();
$results = $first->unionAll($second)->get();
```

#### 调试

```php
// 查看生成的 SQL（参数已替换，仅供调试）
DB::table('users')->where('id', 1)->toSql();    // SELECT * FROM `zap_users` WHERE `id`=1

// dump 调试
DB::table('users')->where('id', 1)->dump();      // 输出 SQL 并继续
DB::table('users')->where('id', 1)->dd();         // 输出 SQL 并终止

// 获取绑定参数
$bindings = DB::table('users')->where('id', 1)->getBindings();
```

### DB Facade 原始 SQL 执行

```php
// SELECT
$users = DB::select('SELECT * FROM users WHERE status=?', [1]);

// 执行 UPDATE / DELETE / DDL，返回受影响行数
DB::exec('UPDATE users SET login_count=login_count+1 WHERE id=?', [1]);
DB::exec('DELETE FROM sessions WHERE expired_at < NOW()');

// 执行 INSERT 并返回新 ID
$id = DB::execInsert('INSERT INTO logs (msg) VALUES (?)', ['start']);

// PDO Statement
$stm = DB::statement('SELECT * FROM users WHERE id=?', [1]);
```

### 直接 ZPDO 操作

```php
$db = DB::connection();

// 原始查询
$db->select('SELECT * FROM {users} WHERE status=?', [1]);
$db->get('SELECT * FROM {users} WHERE id=?', [1]);          // 单行
$db->getAll('SELECT * FROM {users}');                       // 全部
$db->value('SELECT name FROM {users} WHERE id=?', [1]);     // 单列值

// CRUD
$id = $db->insert('users', ['name' => '张三', 'email' => 'zs@a.com']);
$db->update('users', ['status' => 1], 'id=?', [10]);
$db->delete('users', 'status=0 AND created_at < ?', ['2023-01-01']);
$rows = $db->count('users', 'status=?', [1]);

// 批量插入
$db->batchInsert('users', [
    ['name' => 'A', 'email' => 'a@a.com'],
    ['name' => 'B', 'email' => 'b@b.com'],
]);

// Upsert（MySQL/PGSQL）
$db->upsert('users', ['id' => 1, 'name' => '新名字'], ['name' => '新名字']);

// REPLACE INTO
$db->replace('users', ['id' => 1, 'name' => '替换']);

// Key-value pair
$db->keyPair('settings', 'key, value');

// Schema 操作
$db->getTables();
$db->getTableStructure('users');
$db->getTableColumns('users');
$db->renameTable('old_users', 'new_users');
$db->dropTable('temp');
$db->truncateTable('logs');
```

### Model（ActiveRecord 风格）

```php
namespace App\Models;

use zap\db\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    // 自动管理时间戳
    protected $timestamps = true;

    // 批量赋值白名单
    protected $fillable = ['name', 'email', 'status'];

    // 批量赋值黑名单
    // protected $guarded = ['id', 'password'];

    // 类型转换
    protected $casts = [
        'is_active'  => 'boolean',
        'metadata'   => 'json',
        'login_count'=> 'int',
    ];
}
```

#### 查找

```php
// 按主键查找
$user = UserModel::findById(1);          // 返回 null 或 实例
$user = UserModel::findOrFail(1);        // 未找到抛 RuntimeException
$user = UserModel::findOrNew(1);         // 未找到返回新实例（带 id）

// findBy 动态方法
$user  = UserModel::findByEmail('zs@a.com');              // 返回数组
$user  = UserModel::findByIdAndStatus(1, 'active');      // 多条件

// 查询
$users = UserModel::all();               // 全部
$users = UserModel::first();             // 第一条
$count = UserModel::countBy(['status' => 1]);
$exists = UserModel::exists(['email' => 'zs@a.com']);
UserModel::destroy(5);                   // 按主键删除
```

#### 实例操作

```php
$user = new UserModel();
$user->name  = '新用户';
$user->email = 'new@example.com';
$user->save();

// 批量赋值（受 fillable/guarded 约束）
$user = new UserModel();
$user->fill(['name' => '张三', 'email' => 'zs@a.com']);
$user->save();

// 更新
$user = UserModel::findById(1);
$user->name = '改名';
$user->save();

// 删除
$user = UserModel::findById(1);
$user->delete();

// 刷新
$user->refresh();       // 用数据库数据覆盖当前实例
$fresh = $user->fresh(); // 返回新的实例
```

#### 时间戳

```php
class PostModel extends Model
{
    protected $timestamps = true;
    // 自动维护 created_at 和 updated_at
    // 列名可通过 const CREATED_AT / UPDATED_AT 自定义
}
```

#### 序列化

```php
$arr  = $user->toArray();      // ['id' => 1, 'name' => '张三', ...]
$json = $user->toJson();       // JSON 字符串（Unicode 不转义）
$json = json_encode($user);    // 同样输出 JSON
echo $user;                    // __toString → toJson()
```

### 事务

```php
// 闭包事务（自动提交/回滚）
DB::transaction(function () {
    UserModel::findById(1)->delete();
    LogModel::findById(1)->delete();
});

// 手动事务
DB::beginTransaction();
try {
    UserModel::findById(1)->delete();
    LogModel::findById(1)->delete();
    DB::commit();
} catch (\Throwable $e) {
    DB::rollBack();
    throw $e;
}
```

### 查询日志

```php
DB::enableQueryLog();

DB::table('users')->where('id', 1)->get();
DB::table('posts')->limit(10)->get();

$log = DB::getQueryLog();
// [['sql' => '...', 'params' => [...], 'time' => 1234567890.1234], ...]

DB::flushQueryLog();
DB::disableQueryLog();
```

### 多数据库连接

```php
DB::connection('mysql')->table('users')->get();
DB::connection('pgsql')->table('logs')->get();

// 连接信息
DB::connectionInfo();  // [driver, server, version, ...]
```

---

## 视图

Zap 提供轻量级原生 PHP 模板引擎，同时支持 Twig。具有布局继承、块系统、部分包含、HTML 转义等特性。

### 渲染视图

```php
use zap\view\View;

// 控制器中渲染
return view('users.profile', ['user' => $user]);       // 直接输出
$html = view('emails.welcome', $data, true);            // 返回字符串

// 链式调用
$html = View::make('users.profile')
    ->with('user', $user)
    ->with('role', 'admin')
    ->withLayout('layouts.main')
    ->fetch();

// 直接输出
View::make('home')->with('title', '首页')->show();

// 检查模板是否存在
if (View::exists('admin.dashboard')) { ... }

// 第一个存在的模板（备选逻辑）
$tpl = View::first('custom.home', 'default.home');      // 优先 custom
```

### 布局 & 块

```php
// app/views/layouts/main.php
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->e($title) ?></title>
    <?= $this->block('head') ?>
</head>
<body>
    <header><?= $this->include('partials.header') ?></header>
    <main><?= $this->block('content') ?></main>
    <footer><?= $this->include('partials.footer') ?></footer>
    <?= $this->block('scripts') ?>
</body>
</html>

// app/views/home.php
<?php $this->layout('layouts.main') ?>

<?php $this->beginBlock('head') ?>
<link rel="stylesheet" href="/css/home.css">
<?php $this->endBlock() ?>

<h1><?= $this->e($title) ?></h1>
<p><?= $this->e($content) ?></p>

<?php $this->beginBlock('scripts') ?>
<script src="/js/home.js"></script>
<?php $this->endBlock() ?>
```

### 部分包含

```php
// 包含子模板（内容自动加入页面）
$this->include('partials.sidebar');

// 包含并返回内容（不参与布局）
$nav = $this->partial('partials.nav', ['active' => 'home']);

// 控制器中独立渲染局部
$sidebar = View::make('partials.sidebar')->fetch();
```

### 模板中的安全工具

```php
// HTML 转义（XSS 防护）
<?= $this->e($userInput) ?>
<?= $this->esc($potentialXss) ?>
```

### 全局共享数据

```php
// 应用启动时注册，所有视图可见
View::share('appName', 'My App');
View::share('currentUser', $user);

// 模板中直接使用
<?= $currentUser['name'] ?>
```

### 模板路径

```php
// 默认搜索路径：app/views/
// 自动按顺序查找：$path/{name}.php → $path/{name}.html → $path/{name}.twig

// 添加自定义路径
View::addPath('/path/to/extra/views');          // 高优先级（插到头部）
View::addPath('/path/to/vendor/views', true);   // 低优先级（追加末尾）

// 主题支持
// config.php: ['theme' => 'default']
// → 自动追加 themes/default/ 到搜索路径头部

// 注册自定义扩展名
View::registerExtension('phtml');
```

### 内联模板

```php
$html = View::renderString('<h1><?= $title ?></h1>', ['title' => 'Hello']);
$html = View::renderString('
<?php foreach($items as $item): ?>
    <li><?= $this->e($item) ?></li>
<?php endforeach ?>
', ['items' => ['A', 'B', 'C']]);
```

### Twig 引擎

使用 `.twig` 扩展名自动切换为 Twig 渲染器：

```php
// app/views/emails/welcome.twig
<html>
<body>
    <h1>Hello {{ name }}!</h1>
</body>
</html>

// 配置
// config/twig.php
return [
    'template_paths' => [
        'emails' => base_path('app/views/emails'),
    ],
    'options' => [
        'cache' => base_path('storage/cache/twig'),
        'debug' => config('config.debug'),
    ],
    'extensions' => [
        \App\Twig\MyExtension::class,
    ],
];
```

### 错误模板

框架内置错误页面，位于 `src/resources/views/errors/`：
- `error.php` — 通用错误
- `exception.php` — 异常详情（含代码高亮）

### 静态方法速查

| 方法 | 说明 |
|------|------|
| `View::make($name, $data)` | 创建 View 实例 |
| `View::render($name, $data, $return)` | 渲染并输出/返回 |
| `View::exists($name)` | 检查模板是否存在 |
| `View::first(...$names)` | 返回第一个存在的模板名 |
| `View::renderString($str, $data)` | 渲染内联 PHP 字符串 |
| `View::share($key, $value)` | 全局共享数据 |
| `View::paths($path)` | 获取/添加搜索路径 |
| `View::addPath($path, $append)` | 添加搜索路径 |
| `View::clearPaths()` | 清空搜索路径 |
| `View::registerExtension($ext)` | 注册自定义扩展名 |
| `View::autoIncludePath($bool)` | 是否修改 include_path（默认关闭） |

---

## 配置

```php
// 读取配置（支持点号分隔）
$debug = config('config.debug', false);
$host  = config('database.mysql.host', '127.0.0.1');

// 动态设置
config(['app.name' => 'Zap App']);

// 清除配置缓存
\zap\Config::clearCache();
```

> 配置按需懒加载，同一文件只会被加载一次。

---

## 日志

```php
use zap\Log;

// 级别：DEBUG / INFO / NOTICE / WARNING / ERROR / CRITICAL / ALERT / EMERGENCY
Log::info('用户登录', ['user_id' => 123]);
Log::warning('磁盘空间不足');
Log::error('支付失败', ['order_id' => 456]);
Log::debug('SQL执行时间', ['sql' => $sql, 'time' => 0.05]);

// 配置（config/log.php）
return [
    'default' => 'app',
    'level'   => 200,   // INFO 级别
    'path'    => VAR_PATH . '/logs/app.log',
    'app' => [
        'handler' => \Monolog\Handler\StreamHandler::class,
        'params'  => [VAR_PATH . '/logs/app.log', 200],
    ],
];
```

> Monolog 为可选依赖。未安装时，框架使用内置 `SimpleLogger` 将日志写入文件或 `error_log`。

---

## 缓存

```php
use zap\facades\Cache;

// PSR-16 风格接口
Cache::set('key', 'value', 3600);     // 设置，TTL 3600 秒
$value = Cache::get('key', '默认值');  // 获取
Cache::has('key');                     // 是否存在
Cache::delete('key');                  // 删除
Cache::clear();                        // 清空

// 批量操作
Cache::setMultiple(['a' => 1, 'b' => 2], 3600);
$items = Cache::getMultiple(['a', 'b']);

// 配置（config/cache.php）
return [
    'default' => 'file',          // file | redis
    'stores' => [
        'file' => [
            'path' => VAR_PATH . '/cache',
        ],
        'redis' => [
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'password' => null,
            'database' => 0,
            'prefix'   => 'zap:',
        ],
    ],
];
```

---

## Session & Flash 消息

```php
use zap\http\Session;

// 读取
Session::get('user_id');
Session::all();

// 写入
Session::set('user_id', 123);

// 删除
Session::delete('user_id');
Session::destroy();

// Flash 消息（一次性）
session()->setFlash('操作成功', 'success');    // 设置
session()->getFlash('message');                // 读取
```

---

## Hooks 系统

WordPress 风格的过滤器和动作系统：

```php
use zap\component\Hooks;

// 添加过滤器
Hooks::add_filter('content_format', function($content) {
    return nl2br($content);
});

// 添加动作
Hooks::add_action('user_registered', function($user) {
    Log::info('新用户注册', ['user' => $user]);
});

// 应用过滤器
$content = Hooks::apply_filters('content_format', $rawContent);

// 触发动作
Hooks::do_action('user_registered', $user);
```

---

## 中间件

```php
namespace App\Middlewares;

use zap\http\Middleware;

class AuthMiddleware implements Middleware
{
    public function handle($request, \Closure $next)
    {
        if (!isset($_SESSION['user'])) {
            return redirect('/login');
        }
        return $next($request);
    }
}

// 注册（config/route.php 或启动时）
Route::middleware('auth', AuthMiddleware::class);

// 使用
Route::group('/admin', function() {
    // ...
})->middleware('auth');

// 或给单条路由
Route::get('/dashboard', 'DashboardController@index')->middleware('auth');
```

---

## 控制台命令

### 入口文件 `console`

```php
#!/usr/bin/env php
<?php

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('VAR_PATH', ROOT_PATH . '/var');
define('VENDOR_PATH', ROOT_PATH . '/vendor');

require_once VENDOR_PATH . '/autoload.php';

$console = new \zap\console\Console(ROOT_PATH);
$console->addCommand('app/commands', 'app');
exit($console->execute());
```

### 创建命令

```php
namespace App\Commands;

use zap\console\Command;
use zap\console\Input;
use zap\console\Output;

class HelloCommand extends Command
{
    public function configure(): void
    {
        $this->setName('hello')
             ->setDescription('输出欢迎信息')
             ->addArgument('name', '名称', true)
             ->addArgument('greeting', '问候语', false, 'Hello')
             ->addOption('uppercase', 'u', '转为大写输出');
    }

    public function execute(Input $input, Output $output): int
    {
        $name = $input->getArgument(0);
        $greeting = $input->getArgument(1) ?? 'Hello';

        $message = "{$greeting}, {$name}!";

        if ($input->getOption('uppercase')) {
            $message = strtoupper($message);
        }

        $output->success($message);
        return self::SUCCESS;
    }
}
```

### 运行命令

```bash
# 基本执行
php console hello zap

# 传入多个参数
php console hello zap "Good morning"

# 使用选项
php console hello zap -u
php console hello zap --uppercase

# 查看帮助
php console hello -h
php console hello --help

# 调试模式（显示详细输出）
php console hello zap -v     # 一般详情
php console hello zap -vv    # 更详细
php console hello zap -vvv   # 调试级别

# 列表所有可用命令
php console
php console list
```

### 命令配置 API

```php
public function configure(): void
{
    $this
        // 命令名
        ->setName('hello')
        // 描述（在列表命令中显示）
        ->setDescription('输出欢迎信息')
        // 位置参数：名称、描述、是否必填、默认值
        ->addArgument('name', '名称', true)
        ->addArgument('title', '标题', false, 'Mr.')
        // 命名选项：名称、短选项、描述、默认值
        ->addOption('uppercase', 'u', '转为大写')
        ->addOption('times', 't', '重复次数', 1);
}
```

### Input API

```php
class MyCommand extends Command
{
    public function execute(Input $input, Output $output): int
    {
        // 位置参数（0-based 索引）
        $firstName = $input->getArgument(0);
        $secondArg = $input->getArgument(1, '默认值');
        $allArgs   = $input->getArguments();  // 所有位置参数数组

        // 命名选项
        $uppercase = $input->getOption('uppercase');
        $times     = $input->getOption('times', 1);
        $allOpts   = $input->getOptions();

        // 检查是否存在
        $input->hasOption('verbose');
        $input->hasParam('v');

        // 兼容旧接口（1-based）
        $first = $input->getParam(1);

        return self::SUCCESS;
    }
}
```

### Output API

```php
public function execute(Input $input, Output $output): int
{
    // 基本输出
    $output->writeln('普通文本');
    $output->write('不换行...');

    // 带颜色标签的输出
    $output->writeln('<info>信息</info>');
    $output->writeln('<error>错误</error>');
    $output->writeln('<warning>警告</warning>');
    $output->writeln('<success>成功</success>');
    $output->writeln('<debug>调试信息</debug>');

    // 快捷彩色方法
    $output->info('信息消息');
    $output->error('错误消息');     // 输出到 stderr
    $output->warning('警告消息');
    $output->success('成功消息');
    $output->debug('调试消息');

    // 格式化输出
    $output->printf('共处理 %d 条记录', $count);

    // 按详细级别输出
    $output->writelnV('详细信息（-v）');    // 需 -v
    $output->writelnVV('更详细（-vv）');      // 需 -vv
    $output->writelnVVV('调试详情（-vvv）');  // 需 -vvv

    // 检测颜色支持
    if ($output->hasColorSupport()) {
        $output->writeln('<red>红色文字</red>');
    }

    return self::SUCCESS;
}
```

### 颜色样式

当终端支持颜色时，以下标签自动转为 ANSI 彩色输出（不支持时自动去除标签）：

| 标签 | 效果 | 颜色 |
|---|---|---|
| `<info>...</info>` | 信息 | 绿色 |
| `<success>...</success>` | 成功 | 绿色 |
| `<error>...</error>` | 错误 | 红色 |
| `<warning>...</warning>` | 警告 | 黄色 |
| `<comment>...</comment>` | 注释 | 黄色 |
| `<debug>...</debug>` | 调试 | 灰色 |
| `<red>...</red>` | 红色 | 红色 |
| `<green>...</green>` | 绿色 | 绿色 |
| `<yellow>...</yellow>` | 黄色 | 黄色 |
| `<blue>...</blue>` | 蓝色 | 蓝色 |
| `<magenta>...</magenta>` | 品红 | 品红 |
| `<cyan>...</cyan>` | 青色 | 青色 |
| `<white>...</white>` | 白色 | 白色 |
| `<gray>...</gray>` | 灰色 | 灰色 |

> 设置环境变量 `NO_COLOR=1` 可禁用彩色输出。

### 注册命令目录

```php
$console = new \zap\console\Console(ROOT_PATH);

// 注册命令目录（路径 => 命名空间）
$console->addCommand('app/commands', 'app');

// 多个命令目录
$console->addCommand('vendor/my-package/src/commands', 'my-package');

$console->execute();
```

### 命令命名规则

- 命令类放在注册的目录下，文件名即命令名
- `app/commands/HelloCommand.php` → 命令名 `HelloCommand`
- 运行时：`php console HelloCommand arg1`
- 支持 `vendor:CommandName` 格式（适合第三方包）

### 自定义默认命令

```php
$console->setDefaultCommand(MyDefaultCommand::class);
```

> `--version` / `-V` 为内置命令，直接输出版本号，无需注册。

---

## Facades

```php
use zap\facades\Cache;       // 缓存
use zap\facades\Date;        // 日期时间
use zap\facades\Url;         // URL 生成（UrlHelper）
```

### URL Facade 速查

| 方法 | 说明 | 示例 |
|---|---|---|
| `Url::base($url?)` | 获取/设置基础 URL | `Url::base('https://example.com')` |
| `Url::home()` | 首页 URL | `Url::home()` |
| `Url::full()` | 完整请求 URL（含协议主机路径） | `Url::full()` |
| `Url::previous()` | 上一页 Referer | `Url::previous()` |
| `Url::current()` | 当前请求 URI 路径 | `Url::current()` |
| `Url::route($name, $params, $absolute)` | 命名路由 URL | `Url::route('profile', ['id' => 1])` |
| `Url::action($action, $query, $path)` | 控制器动作 URL | `Url::action('UserController@show', [], ['id'=>5])` |
| `Url::to($format, $params, $query)` | 格式化 URL | `Url::to('/user/{id}/edit', ['id'=>10])` |
| `Url::isActive($action, $class?)` | 路由激活状态检测 | `Url::isActive('/admin*', 'nav-active')` |
| `Url::secure($path)` | 生成 HTTPS URL | `Url::secure('/admin')` |
| `Url::controller()` | 当前控制器名 | `Url::controller()` |
| `Url::method()` | 当前方法名 | `Url::method()` |
| `Url::getRouteData($name?)` | 路由数据 | `Url::getRouteData('profile')` |

---

## 辅助函数

```php
// 路径
base_path('app/views');        // 项目根路径
config_path('database.php');   // 配置路径
app_path('models/User.php');   // 应用路径
storage_path('logs/app.log');  // 存储路径

// 应用 & 依赖
app();                          // 获取 App 实例
app('router');                  // 获取容器中的服务

// 配置
config('database.default');
config(['app.debug' => true]);

// 路由
route('profile');               // 命名路由 URL
url('/users');                  // 完整 URL

// 响应
redirect('/login');             // 重定向
view('home', ['name' => 'Zap']);// 视图
json(['code' => 0]);            // JSON 响应

// 其他
dd($data);                      // dump & die
env('APP_ENV', 'production');   // 环境变量
value(function() { ... });      // 执行回调

// 国际化（需先加载语言文件）
__('welcome');                                 // 简单翻译
__('user.greeting', ['name' => 'Zap']);        // 带参数翻译
trans_choice('apples', 5);                     // 复数翻译

// 字符串
snake_case('HelloWorld');       // hello_world
camel_case('hello_world');      // helloWorld
studly_case('hello_world');     // HelloWorld
str_contains('hello world', 'world');  // true
str_starts_with('hello', 'he');        // true
str_ends_with('hello', 'lo');          // true
str_limit('very long text', 10);       // very lon...
```

---

## HTML 构建器

流式链式调用，自动转义属性值防止 XSS。

```php
use zap\html\Html;

// 基础元素
echo Html::el('div')->class('card')->id('app')->text('Hello');

// 属性方法
echo Html::el('input')
    ->attr('type', 'email')
    ->attr('placeholder', '请输入邮箱')
    ->class('form-control');

// CSS class 自动去重
echo Html::el('div')->class('btn')->class('btn')->class('primary');
// → <div class="btn primary"></div>

// 布尔属性
echo Html::el('input')->attr('disabled', true);   // → disabled
echo Html::el('input')->attr('disabled', false);  // → 不渲染

// data-* 属性
echo Html::el('div')->data('controller', 'modal')->data('action', 'close');

// 内容 (text 自动转义，html 原样输出)
echo Html::el('span')->text('<script>alert(1)</script>');
// → &lt;script&gt;... 安全输出

// 子节点嵌套
echo Html::el('ul')->append(
    Html::el('li')->text('项目1')->class('active'),
    Html::el('li')->text('项目2'),
);

// 便捷工厂
echo Html::a('/users', '用户列表')->class('nav-link');          // <a>
echo Html::img('/logo.png')->class('logo')->attr('alt', 'Logo'); // <img>
echo Html::input('text', 'username')->class('form-control');     // <input>
echo Html::form('/login', 'POST')->append(                       // <form>
    Html::input('email')->class('form-control'),
    Html::button('登录')->class('btn'),
);
echo Html::select([1 => '启用', 0 => '禁用'], 1);               // <select>
echo Html::ul(['首页', '关于', '联系']);                        // <ul><li>…</li></ul>
echo Html::table([['张三', 28]], ['姓名', '年龄']);             // <table>
echo Html::h(2, '二级标题');                                     // <h2>
```

---

## 照片处理 (Image)

基于 PHP GD 扩展的图片处理组件，支持链式调用、格式转换、滤镜、水印和 EXIF 自动旋转。

> 需要 PHP GD 扩展。JPEG/PNG/GIF 为 GD 内置支持，WebP 需要 GD 编译时启用。

### 基本使用

```php
use zap\image\Image;

// 加载图片
$img = new Image('/path/to/photo.jpg');
// 或静态工厂
$img = Image::from('/path/to/photo.jpg');

// 空白画布
$canvas = Image::canvas(800, 600, 'FFFFFF', 'png');
```

### 缩放 & 裁剪

```php
// 缩放：传入宽或高之一即可等比缩放
$img->resize(800);               // 固定宽度，高度等比
$img->resize(null, 600);         // 固定高度，宽度等比
$img->resize(800, 600);          // 同时指定宽高

// 等比缩放（不留白）
$img->fit(400, 300);             // 按短边适配，决不超出目标范围
$img->fit(400, 300, true);       // 第三个参数 true 允许放大

// 居中裁剪（fill/cover）
$img->fill(400, 300);            // 缩放后居中裁剪
$img->thumb(400, 300, 'top');    // 锚点裁剪：center/top/bottom/left/right/top-left 等

// 正方形裁剪
$img->square(300);               // 按短边出 300x300
$img->square(300, 'top-left');   // 指定锚点

// 矩形裁剪
$img->crop(100, 100, 300, 300);

// 画布扩展
$img->resizeCanvas(800, 600, 'center', 'FFFFFF');
```

### 旋转 & 翻转

```php
$img->rotate(90);                // 顺时针旋转 90°
$img->rotate(45, 'FF0000');      // 旋转并指定背景色

$img->flip();                    // 水平翻转
$img->flop();                    // 垂直翻转
$img->flipBoth();                // 180° 翻转

// 根据 EXIF Orientation 自动修正（手机竖拍纠正）
$img->orient();
```

### 滤镜

```php
$img->grayscale();               // 灰度
$img->invert();                  // 反色
$img->brightness(30);            // 亮度 -255 ~ 255
$img->contrast(-20);             // 对比度 -100 ~ 100
$img->saturation(50);            // 饱和度 -100 ~ 100
$img->blur(5);                   // 高斯模糊（1-10 次）
$img->blurSelective();           // 选择性模糊（保留边缘）
$img->sharpen();                 // 锐化
$img->edgeDetect();              // 边缘检测
$img->emboss();                  // 浮雕
$img->pixelate(10);              // 像素化 / 马赛克
$img->smooth(6);                 // 平滑
$img->meanRemoval();             // 平均去噪
$img->colorize(255, 0, 0);       // 色彩叠加 (R,G,B)
$img->sepia();                   // 复古怀旧色调

// 自定义滤镜
$img->filter(IMG_FILTER_CONTRAST, -30);
```

### 水印 & 叠加

```php
$watermark = new Image('/path/to/logo.png');

// 九宫格定位：topleft / topcenter / topright / middleleft / middlecenter / middleright / bottomleft / bottomcenter / bottomright
$img->watermark($watermark, 'bottomright');
$img->watermark($watermark, 'topleft', 50);   // 50% 不透明度

// 自由叠加
$img->merge($overlay, 100, 200, 70);          // x, y, 不透明度
```

### 文字

```php
// GD 内置字体（大小 1-5）
$img->text('Hello Zap', 10, 20, 5, 'FFFFFF');

// TTF 字体
$img->ttfText('Hello', '/fonts/arial.ttf', 24, 50, 100, 'FF0000');
$img->ttfText('倾斜', '/fonts/arial.ttf', 24, 50, 100, '000000', 30);

// 获取 TTF 文字包围盒
$box = Image::ttfBoundingBox('Hello', '/fonts/arial.ttf', 24);
```

### 格式转换

```php
// 设置输出格式（自动转换）
$img->setOutputFormat('webp');     // 转 WebP
$img->setOutputFormat('jpeg');     // 转 JPEG
$img->setOutputFormat('png');      // 转 PNG
$img->setOutputFormat('gif');      // 转 GIF

// 控制质量
$img->setQuality(85);              // 0-100（默认 90）
```

### 输出 & 保存

```php
// 保存到文件
$img->save('/output/result.jpg');
$img->save('/output/result.jpg', 95);      // 指定质量

// 保存到目录（使用原始文件名）
$img->savePath('/output/');

// 保存到路径（自动创建目录）
$img->saveFile('/output/images/avatar.png');

// 获取二进制数据
$data  = $img->getImageData();              // 原格式
$data  = $img->getImageData('webp', 80);    // 指定格式+质量

// Base64 Data URI
$uri   = $img->toBase64();
$uri   = $img->toBase64('png');

// 浏览器输出
$img->toBrowser();                           // 直接输出
$img->download('photo.jpg');                 // 强制下载
$img->download('photo.jpg', null, 90);       // 指定质量
```

### 复制 & 资源管理

```php
// 复制（独立的 GD 资源，互不影响）
$copy = $img->copy();
$copy->resize(200)->save('/output/thumb.jpg');

// 显式销毁（释放内存）
$img->destroy();

// 析构时自动释放，无需手动调用
```

### 元数据

```php
// 图片信息
$info = $img->info();      // [file, dirname, basename, mime, width, height, bits, ...]
$info = $img->toArray();   // 同 info()

// EXIF（仅 JPEG）
$exif = $img->getExif();

// 分辨率（DPI）
$res = $img->getResolution();

// Getter 方法
$img->getWidth();
$img->getHeight();
$img->getMimeType();
$img->getExtName();
$img->getFile();
$img->getImage();          // GD 资源
$img->getBits();
```

### 完整示例：用户头像处理

```php
use zap\image\Image;

$avatar = new Image('/tmp/upload.jpg');

$avatar
    ->orient()                                   // 修正 EXIF 方向
    ->square(400, 'center')                      // 裁剪为正方形
    ->resize(200, 200)                           // 缩放到目标尺寸
    ->sharpen()                                  // 轻微锐化
    ->setOutputFormat('webp')                    // 转为 WebP
    ->setQuality(85)
    ->saveFile('/public/avatars/user_123.webp');

// 同时生成缩略图
$avatar->copy()
    ->resize(64, 64)
    ->saveFile('/public/avatars/user_123_thumb.webp');
```

---

## 加密

```php
use zap\crypto\OpenSSL;

$encrypted = OpenSSL::encrypt('敏感数据', 'your-secret-key');
$decrypted = OpenSSL::decrypt($encrypted, 'your-secret-key');
```

默认使用 AES-256-CBC 算法。

---

## 错误处理

框架内置错误处理器，根据环境模式切换行为：

```php
(new \zap\App())->environment('development');  // 显示详细错误
(new \zap\App())->environment('production');   // 不显示敏感信息
```

---

## URL Rewrite

### Apache

```apacheconf
Options +FollowSymLinks -Indexes
RewriteEngine On

RewriteCond %{HTTP:Authorization} .
RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,PT,L]
```

### Nginx

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

---

## License

MIT
