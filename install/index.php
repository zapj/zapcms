<?php

require '../vendor/autoload.php';

$app = new \zap\App(realpath('../'));

$action = $_GET['action'] ?? 'index';
$action = $action . "Action";

if (function_exists($action)) {
    config_set('config.theme', false);
    \zap\view\View::paths(realpath('views/'));
    call_user_func($action);
} else {
    exit('404 Page Not found');
}


function indexAction()
{
    $data = [];
    view("index", $data);
}

function checkAction()
{
    $data = [];
    view("check", $data);
}

function databaseAction()
{
    $data = [];
    view("database", $data);
}

function doneAction()
{
    $data = [];
    view("done", $data);
}


function checkDatabaseConnectionAction()
{
    $db = \zap\http\Request::post('db', []);
    $website = \zap\http\Request::post('website', []);
    $dbname = $db['dbname'] ?? 'zapcms';
    $host = $db['host'] ?? 'localhost';
    $username = $db['username'] ?? 'root';
    $password = $db['password'] ?? '';
    $port = $db['port'] ?? '3306';
    $driver = $db['driver'] ?? 'mysql';
    $dbOptions = [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ];
    $pdo = null;
    try {
        if ($driver === 'mysql') {
            $dbOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8";
            $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password, $dbOptions);
            $pdo->query("USE {$dbname}");
            response()->withJson(['code' => 0, 'msg' => '数据库连接成功']);
        } else if ($driver === 'sqlite') {
            $db_file = var_path("data/{$dbname}.db");
            if (!is_writable(var_path("data"))) {
                response()->withJson(['code' => 1, 'msg' => '数据库创建失败,' . var_path("data") . '  目录不可写入']);
            }
            $pdo = new PDO("sqlite:{$db_file}", null, null, $dbOptions);
            response()->withJson(['code' => 0, 'msg' => '数据库连接成功']);
        } else {
            response()->withJson(['code' => 1, 'msg' => '暂不支持该驱动类型']);
        }
    } catch (PDOException $e) {
        if (intval($e->getCode()) === 2002) {
            response()->withJson(['code' => 1, 'msg' => '数据库链接失败', 'exception' => "{$host}:{$port} 主机名无法解析"]);
        } else if (intval($e->getCode()) === 42000) {
            try {
                $pdo->query("CREATE DATABASE IF NOT EXISTS {$dbname}");
                response()->withJson(['code' => 0, 'msg' => "数据库链接成功,数据库{$dbname}已创建"]);
            } catch (PDOException $e) {
                response()->withJson(['code' => 1, 'msg' => '数据库不存在，创建失败', 'exception' => $e->getMessage()]);
            }
            response()->withJson(['code' => 1, 'msg' => '数据库链接失败', 'exception' => $e->getMessage()]);
        }
        response()->withJson(['code' => 1, 'msg' => '数据库链接失败' . $e->getCode(), 'exception' => $e->getMessage()]);
    }
}

function createDBSchemaBaseDataAction()
{
    $db = \zap\http\Request::post('db', []);
    $website = \zap\http\Request::post('website', []);
    $dbname = $db['dbname'] ?? 'zapcms';
    $host = $db['host'] ?? 'localhost';
    $username = $db['username'] ?? 'root';
    $password = $db['password'] ?? '';
    $port = $db['port'] ?? '3306';
    $driver = $db['driver'] ?? 'mysql';
    $prefix = $db['prefix'] ?? '';

    $website_username = $website['username'];
    $website_password = $website['password'];

    config_set('database', [
        'default' => 'default',
        'connections' => []
    ]);
    if ($driver === 'mysql') {
        config_set('database.connections.default', [
            'driver' => 'mysql',
            'host' => $host,
            'port' => $port,
            'dbname' => $dbname,
            'user' => $username,
            'password' => $password,
            'prefix' => $prefix,
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
        ]);
    } else if ($driver === 'sqlite') {
        config_set('database.connections.default', [
            'driver' => 'sqlite',
            'dsn' => sprintf("sqlite:%s", var_path("data/{$dbname}.db")),
            'prefix' => $prefix,
            'charset' => 'utf8',
            'collate' => 'utf8_general_ci',
        ]);
    }


    try {
        ob_start();
        \zap\db\Schema::connection('default');
        $schema = new \zap\cms\CreateTables();
        $schema->createSchema();
        $schema->installBaseData();
        $schema->installDemoData();
        $output = ob_get_clean();

        //写入配置文件

        if (is_file(config_path('database.php'))) {
            rename(config_path('database.php'), config_path('backup.database.php'));
        }
        $database = config('database');
        \zap\cms\ZapConfig::createConfig($database, config_path('database.php'));
        //更新站点名称
        \zap\Option::update('website.title', $website['title'] ?? 'ZAP CMS');
        \zap\Option::update('website.slogan', $website['slogan'] ?? 'OpenSource CMS');
        \zap\Option::update('website.email',$website['email'] ?? '');
        \zap\DB::update('admin', ['username' => $website_username, 'password' => \zap\util\Password::hash($website_password)], ['id' => 1]);

        response()->withJson(['code' => 0, 'msg' => "<br/><strong>安装完成</strong><br/>" . nl2br($output)]);

    } catch (PDOException $e) {
        response()->withJson(['code' => 1, 'msg' => '创建表结构与导入基础数据失败', 'exception' => $e->getMessage()]);
    }


}
