<?php 
namespace Beeralex\Core\Config;

class ConfigLoaderFactory
{
    public function createOptionsLoader(?string $configDir = null): OptionsLoader
    {
        return new OptionsLoader($configDir);
    }

    public function createArrayConfigLoader(?string $configDir = null): ArrayConfigLoader
    {
        return new ArrayConfigLoader($configDir);
    }
}