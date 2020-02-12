<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit559b41ec6819813f07be280b0253b804
{
    public static $prefixLengthsPsr4 = array (
        'V' => 
        array (
            'VPA\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'VPA\\' => 
        array (
            0 => __DIR__ . '/..' . '/VPA',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit559b41ec6819813f07be280b0253b804::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit559b41ec6819813f07be280b0253b804::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}