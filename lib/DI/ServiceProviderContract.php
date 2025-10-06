<?php

namespace Beeralex\Core\DI;

interface ServiceProviderContract
{
    /**
     * Зарегистрировать зависимости в сервис-локаторе
     */
    public function register(): void;
}
