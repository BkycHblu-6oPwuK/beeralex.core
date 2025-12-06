<?php

namespace Beeralex\Core\Config;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\Dictionary;

/**
 * Абстрактный класс для работы с настройками модуля
 * Наследуется от Dictionary для поддержки ArrayAccess, Iterator, Countable, JsonSerializable
 */
abstract class AbstractOptions extends Dictionary
{
    public final function __construct()
    {
        $moduleId = $this->getModuleId();
        if ($moduleId === '') {
            throw new \InvalidArgumentException('Module ID must be defined.');
        }
        
        $options = array_merge(Option::getDefaults($moduleId), Option::getForModule($moduleId));
        
        // Инициализируем Dictionary
        parent::__construct($options);
        
        $this->mapOptions($options);
        $this->validateOptions();
    }

    abstract protected function mapOptions(array $options): void;
    abstract public function getModuleId(): string;

    protected function validateOptions(): void {}

    /**
     * Магический геттер для доступа к опциям как к свойствам
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Магический сеттер для установки опций как свойств
     */
    public function __set(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Проверка существования опции
     */
    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * Удаление опции
     */
    public function __unset(string $name)
    {
        $this->offsetUnset($name);
    }

    public function __jsonSerialize()
    {
        return $this->toArray();
    }
}
