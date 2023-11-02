<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit501184272d4d7ed612cf428f1bb86753
{
    public static $files = array (
        '320cde22f66dd4f5d3fd621d3e88b98f' => __DIR__ . '/..' . '/symfony/polyfill-ctype/bootstrap.php',
        '0e6d7bf4a5811bfa5cf40c5ccd6fae6a' => __DIR__ . '/..' . '/symfony/polyfill-mbstring/bootstrap.php',
        'a4a119a56e50fbb293281d9a48007e0e' => __DIR__ . '/..' . '/symfony/polyfill-php80/bootstrap.php',
        'ed7610ef029ae6b6d5177039e3fb3277' => __DIR__ . '/..' . '/zapj/zap-php-framework/src/functions.php',
        '42958137b6d13be8aa87d316c453d768' => __DIR__ . '/../..' . '/app/zap/functions.php',
    );

    public static $prefixLengthsPsr4 = array (
        'z' => 
        array (
            'zap\\' => 4,
        ),
        'm' => 
        array (
            'mods\\' => 5,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'T' => 
        array (
            'Twig\\' => 5,
        ),
        'S' => 
        array (
            'Symfony\\Polyfill\\Php80\\' => 23,
            'Symfony\\Polyfill\\Mbstring\\' => 26,
            'Symfony\\Polyfill\\Ctype\\' => 23,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'zap\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app/zap',
            1 => __DIR__ . '/..' . '/zapj/zap-php-framework/src',
        ),
        'mods\\' => 
        array (
            0 => __DIR__ . '/../..' . '/mods',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
        'Twig\\' => 
        array (
            0 => __DIR__ . '/..' . '/twig/twig/src',
        ),
        'Symfony\\Polyfill\\Php80\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-php80',
        ),
        'Symfony\\Polyfill\\Mbstring\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-mbstring',
        ),
        'Symfony\\Polyfill\\Ctype\\' => 
        array (
            0 => __DIR__ . '/..' . '/symfony/polyfill-ctype',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
    );

    public static $classMap = array (
        'Attribute' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Attribute.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'PhpToken' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/PhpToken.php',
        'Stringable' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/Stringable.php',
        'UnhandledMatchError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/UnhandledMatchError.php',
        'ValueError' => __DIR__ . '/..' . '/symfony/polyfill-php80/Resources/stubs/ValueError.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit501184272d4d7ed612cf428f1bb86753::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit501184272d4d7ed612cf428f1bb86753::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit501184272d4d7ed612cf428f1bb86753::$classMap;

        }, null, ClassLoader::class);
    }
}
