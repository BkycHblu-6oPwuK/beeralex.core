<?php

namespace Beeralex\Core\Config;

class OptionsLoader extends BaseConfigLoader
{
    protected function validate($data, string $fileName)
    {
        if (!$data instanceof Schema) {
            throw new \InvalidArgumentException(
                "File {$fileName} must return instance of " . Schema::class
            );
        }

        return $data;
    }

    protected function defaultValue(): null
    {
        return null;
    }
}
