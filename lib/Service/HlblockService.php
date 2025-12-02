<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Loader;

class HlblockService
{
    public function __construct() 
    {
        Loader::includeModule("highloadblock");
    }

    /**
     * Получает ID хайлоадблока по его имени
     * @return int
     * @throws \Exception
     */
    public function getHlblockIdByName(string $hlblockName): int
    {
        $row = HighloadBlockTable::query()
            ->setSelect(['ID'])
            ->where('NAME', $hlblockName)
            ->setCacheTtl(86400)
            ->exec()
            ->fetch();

        if (!$row) {
            throw new \Exception("HL-блок с именем '{$hlblockName}' не найден");
        }

        return (int)$row['ID'];
    }

    /**
     * Получает класс хайлоадблока по его ID
     * @return string|\Bitrix\Main\ORM\Data\DataManager
     * @throws \Exception
     */
    public function getHlblockById(int $hlblockId): string
    {
        $hlblock = HighloadBlockTable::getByPrimary($hlblockId, [
            'cache' => ['ttl' => 86400]
        ])->fetch();

        if (!$hlblock) {
            throw new \Exception("HL-блок с ID {$hlblockId} не найден");
        }

        $entity = HighloadBlockTable::compileEntity($hlblock);
        $dataClass = $entity->getDataClass();

        return $dataClass;
    }

    /**
     * Получает класс хайлоадблока по его имени таблицы
     * @return string|\Bitrix\Main\ORM\Data\DataManager
     * @throws \Exception
     */
    public function getHlBlockByTableName(string $tableName): string
    {
        $hlblock = HighloadBlockTable::query()
            ->setSelect(['ID'])
            ->where('TABLE_NAME', $tableName)
            ->setCacheTtl(86400)
            ->exec()
            ->fetch();

        if (!$hlblock) {
            throw new \Exception("HL-блок с таблицей '{$tableName}' не найден");
        }

        return $this->getHlblockById((int)$hlblock['ID']);
    }

    /**
     * получает массив классов хайлоадблоков по их именам таблиц
     */
    public function getHlBlocksByTableNames(array $tableNames): array
    {
        if(empty($tableNames)) {
            return [];
        }
        $hlblocks = [];
        $blocks = HighloadBlockTable::query()
            ->setSelect(['*'])
            ->whereIn('TABLE_NAME', $tableNames)
            ->setCacheTtl(86400)
            ->exec()
            ->fetchAll();

        foreach ($blocks as $block) {
            $hlblocks[$block['TABLE_NAME']] = HighloadBlockTable::compileEntity($block)->getDataClass();
        }
        
        return $hlblocks;
    }

    /**
     * Получает класс хайлоадблока по его имени
     * @return string|\Bitrix\Main\ORM\Data\DataManager
     * @throws \Exception
     */
    public function getHlblockByName(string $hlblockName): string
    {
        $hlblockId = $this->getHlblockIdByName($hlblockName);
        return $this->getHlblockById($hlblockId);
    }
}
