<?php
declare(strict_types=1);
namespace Beeralex\Core\Traits;

trait TableManagerTrait
{
    /**
     * Удаляет таблицу из базы данных, если она существует
     */
    public static function dropTable() : void
    {
        if (static::tableExists()) {
            $connection = \Bitrix\Main\Application::getConnection();
            $connection->dropTable(static::getTableName());
        }
    }

    /**
     * Создаёт таблицу в базе данных, если она не существует
     */
    public static function createTable() : void
    {
        if (!static::tableExists()) {
            static::getEntity()->createDbTable();
        }
    }

    /**
     * Проверяет, существует ли таблица в базе данных
     */
    public static function tableExists() : bool
    {
        return static::getEntity()->getConnection()->isTableExists(static::getTableName());
    }
}