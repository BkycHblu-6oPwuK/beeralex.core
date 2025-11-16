<?php

namespace Beeralex\Core\Config;

use Beeralex\Core\Config\Module\Schema\Schema;

class OptionsLoader extends AbstractConfigLoader
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

    protected function defaultValue(): Schema
    {
        return new Schema();
    }
}
