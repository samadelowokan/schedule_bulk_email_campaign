<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitadfdb19cec0ac5a328d06566a1a8dc27
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitadfdb19cec0ac5a328d06566a1a8dc27::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitadfdb19cec0ac5a328d06566a1a8dc27::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitadfdb19cec0ac5a328d06566a1a8dc27::$classMap;

        }, null, ClassLoader::class);
    }
}
