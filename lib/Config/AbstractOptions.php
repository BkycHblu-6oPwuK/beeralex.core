<?php

namespace Beeralex\Core\Config;

use Bitrix\Main\Config\Option;
use Beeralex\Core\Traits\Resourceble;

abstract class AbstractOptions implements \JsonSerializable, \ArrayAccess, \Countable
{
    use Resourceble;

    public final function __construct()
    {
        $moduleId = $this->getModuleId();
        if ($moduleId === '') {
            throw new \InvalidArgumentException('Module ID must be defined.');
        }
        $options = array_merge(Option::getDefaults($moduleId), Option::getForModule($moduleId));
        $this->mapOptions($options);
        $this->validateOptions();
    }

    abstract protected function mapOptions(array $options): void;
    abstract public function getModuleId(): string;

    protected function validateOptions(): void {}

    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->__get($key) ?: $default;
    }
}
