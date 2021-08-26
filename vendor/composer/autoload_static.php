<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc9a5c67dbe530b2953f851ed5a2d0ce6
{
    public static $prefixLengthsPsr4 = array (
        'd' => 
        array (
            'db\\' => 3,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'W' => 
        array (
            'Whoops\\' => 7,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'db\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/db',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src/app',
        ),
        'Whoops\\' => 
        array (
            0 => __DIR__ . '/..' . '/filp/whoops/src/Whoops',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc9a5c67dbe530b2953f851ed5a2d0ce6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc9a5c67dbe530b2953f851ed5a2d0ce6::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc9a5c67dbe530b2953f851ed5a2d0ce6::$classMap;

        }, null, ClassLoader::class);
    }
}
