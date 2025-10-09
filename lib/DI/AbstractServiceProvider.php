<?php

namespace Beeralex\Core\DI;

use Bitrix\Main\DI\ServiceLocator;

abstract class AbstractServiceProvider implements ServiceProviderContract
{
    protected static ?ServiceLocator $locator = null;

    public function __construct()
    {
        if ($this->locator === null) {
            $this->locator = ServiceLocator::getInstance();
        }
    }

    public function register(): void
    {
        $this->registerServices();
    }

    abstract protected function registerServices(): void;

    public function __get($name)
    {
        if ($name === 'locator') {
            return static::$locator;
        }
    }

    public function __set($name, $value)
    {
        if ($name === 'locator') {
            static::$locator = $value;
        }
    }

    /**
     * Зарегистрировать сервис, если он ещё не зарегистрирован
     * @param string $name
     * @param callable $factory фабрика, возвращающая конфигурацию сервиса для ServiceLocator (массив с ключами className и constructorParams)
     */
    protected function bind(string $name, string $className, ?callable $constructFactory = null): void
    {
        if (!$this->locator->has($name)) {
            $this->locator->addInstanceLazy($name, [
                'constructor' => function () use($className, $constructFactory) {
                    if($constructFactory) {
                        return new $className(...$constructFactory());
                    }
                    return new $className();
                },
            ]);
        }
    }
}
