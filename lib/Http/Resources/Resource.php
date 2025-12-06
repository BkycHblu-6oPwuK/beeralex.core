<?php
declare(strict_types=1);
namespace Beeralex\Core\Http\Resources;

use Bitrix\Main\Type\Dictionary;

/**
 * Базовый абстрактный класс для ресурсов API и в целом DTO
 * Наследуется от Dictionary для поддержки ArrayAccess, Iterator, Countable
 */
abstract class Resource extends Dictionary implements ResourceContract
{
    public final function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function make(array $data): static
    {
        return new static($data);
    }

    /**
     * Переопределяем jsonSerialize для вызова toArray
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Получение строкового значения по ключу
     */
    public function getString(string $key): ?string
    {
        $value = $this->get($key);
        return is_string($value) ? $value : null;
    }

    /**
     * Получение целочисленного значения по ключу
     */
    public function getInt(string $key): ?int
    {
        $value = $this->get($key);
        return is_numeric($value) ? (int)$value : null;
    }

    /**
     * Получение целочисленного значения по ключу
     */
    public function getFloat(string $key): ?float
    {
        $value = $this->get($key);
        return is_numeric($value) ? (float)$value : null;
    }

    /**
     * Магический геттер для доступа к значениям как к свойствам
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Магический сеттер для установки значений как свойств
     */
    public function __set(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    /**
     * Проверка существования свойства
     */
    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    /**
     * Удаление свойства
     */
    public function __unset(string $name)
    {
        $this->offsetUnset($name);
    }
}