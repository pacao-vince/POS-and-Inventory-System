<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3b5d6dfb5b560e75c69c889bf65a2f09
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'Mike42\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Mike42\\' => 
        array (
            0 => __DIR__ . '/..' . '/mike42/gfx-php/src/Mike42',
            1 => __DIR__ . '/..' . '/mike42/escpos-php/src/Mike42',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3b5d6dfb5b560e75c69c889bf65a2f09::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3b5d6dfb5b560e75c69c889bf65a2f09::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3b5d6dfb5b560e75c69c889bf65a2f09::$classMap;

        }, null, ClassLoader::class);
    }
}