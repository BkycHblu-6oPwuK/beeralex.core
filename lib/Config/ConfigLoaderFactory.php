<?php
namespace Beeralex\Core\Config;

use Beeralex\Core\Config\OptionsLoader;

class ConfigLoaderFactory
{
    private static array $instances = [];
    
    public static function getOptionsLoader(): OptionsLoader
    {
        return self::$instances[OptionsLoader::class] ??= new OptionsLoader();
    }
    
    public static function getArrayLoader(): ArrayConfigLoader
    {
        return self::$instances[ArrayConfigLoader::class] ??= new ArrayConfigLoader();
    }
}