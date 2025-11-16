<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;

class HlblockService
{
    protected static array $hlblockCodeIdMap = [];
    protected static array $hlblockClassMap = [];

    public function __construct() 
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * Получает ID хайлоадблока по его имени
     *
     * @param string $hlblockName
     * @return int
     * @throws \Exception
     */
    public function getHlblockIdByName(string $hlblockName): int
    {
        if (!isset(static::$hlblockCodeIdMap[$hlblockName])) {
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
    public function getHlblockById(int $hlblockId): string
    {
        if (!isset(static::$hlblockClassMap[$hlblockId])) {

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
    public function getHlblockByName(string $hlblockName): string
    {
        if (!isset(static::$hlblockClassMap[$hlblockName])) {
            $hlblockId = $this->getHlblockIdByName($hlblockName);
            static::$hlblockClassMap[$hlblockName] = $this->getHlblockById($hlblockId);
        }

        return static::$hlblockClassMap[$hlblockName];
    }
}
