<?php
namespace Beeralex\Core\Config;

class ArrayConfigLoader extends BaseConfigLoader
{
    protected function validate($data, string $fileName)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException(
                "File {$fileName} must return an array"
            );
        }
        
        return $data;
    }
    
    protected function defaultValue(): array
    {
        return [];
    }
}