<?php

namespace Beeralex\Core\Helpers;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;

class HlblockHelper
{
    private static $hlblockCodeIdMap = [];
    private static $hlblockClassMap = [];

    private function __construct() {}

    /**
     * Получает ID хайлоадблока по его имени
     *
     * @param string $hlblockName
     * @return int
     * @throws \Exception
     */
    public static function getHlblockIdByName(string $hlblockName): int
    {
        if (!isset(static::$hlblockCodeIdMap[$hlblockName])) {
            Loader::includeModule("highloadblock");

            $row = HighloadBlockTable::getList([
                'select' => ['ID'],
                'filter' => ['NAME' => $hlblockName],
                'cache'  => ['ttl' => 86400],
            ])->fetch();

            if (!$row) {
                throw new \Exception("HL-блок с именем '{$hlblockName}' не найден");
            }

            static::$hlblockCodeIdMap[$hlblockName] = (int)$row['ID'];
        }

        return static::$hlblockCodeIdMap[$hlblockName];
    }

    /**
     * Получает класс хайлоадблока по его ID
     *
     * @param int $hlblockId
     * @return string|\Bitrix\Main\ORM\Data\DataManager
     * @throws \Exception
     */
    public static function getHlblockById(int $hlblockId): string
    {
        if (!isset(static::$hlblockClassMap[$hlblockId])) {
            Loader::includeModule("highloadblock");

            $hlblock = HighloadBlockTable::getByPrimary($hlblockId, [
                'cache' => ['ttl' => 86400]
            ])->fetch();

            if (!$hlblock) {
                throw new \Exception("HL-блок с ID {$hlblockId} не найден");
            }

            $entity = HighloadBlockTable::compileEntity($hlblock);
            $dataClass = $entity->getDataClass();

            static::$hlblockClassMap[$hlblockId] = $dataClass;
        }

        return static::$hlblockClassMap[$hlblockId];
    }

    /**
     * Получает класс хайлоадблока по его имени
     *
     * @param string $hlblockName
     * @return string|\Bitrix\Main\ORM\Data\DataManager
     * @throws \Exception
     */
    public static function getHlblockByName(string $hlblockName): string
    {
        if (!isset(static::$hlblockClassMap[$hlblockName])) {
            $hlblockId = static::getHlblockIdByName($hlblockName);
            static::$hlblockClassMap[$hlblockName] = static::getHlblockById($hlblockId);
        }

        return static::$hlblockClassMap[$hlblockName];
    }
}
