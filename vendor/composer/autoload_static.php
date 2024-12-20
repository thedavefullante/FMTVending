<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita5904f1628862ed0a1854488df935887
{
    public static $prefixLengthsPsr4 = array (
        'E' => 
        array (
            'Etap\\FmtVending\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Etap\\FmtVending\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita5904f1628862ed0a1854488df935887::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita5904f1628862ed0a1854488df935887::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInita5904f1628862ed0a1854488df935887::$classMap;

        }, null, ClassLoader::class);
    }
}
