<?php

namespace Beeralex\Core\Helpers;

use Bitrix\Sale\Internals\StatusTable;

class SaleHelper
{
    private function __construct() {}

    public static function getStatuses(): array
    {
        return StatusTable::query()->setSelect(['ID', 'NAME' => 'STATUS_LANG.NAME'])->where('STATUS_LANG.LID', \Bitrix\Main\Localization\Loc::getCurrentLang() ?: 'ru')->setCacheTtl(86400)->cacheJoins(true)->fetchAll();
    }

    public static function getActivePayment()
    {
        return \Bitrix\Sale\PaySystem\Manager::getList(['filter' => ['ACTIVE' => 'Y']])->fetchAll();
    }

    public static function getActiveDelivery()
    {
        return \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
    }

    public static function getExtraServiceByCode(string $deliveryCode, string $serviceCode)
    {
        $result = \Bitrix\Sale\Delivery\ExtraServices\Table::query()->setSelect(['*'])->where('CODE', $serviceCode)->registerRuntimeField('', new Reference('D', \Bitrix\Sale\Delivery\Services\Table::class, ['=this.DELIVERY_ID' => 'ref.ID']))->where('D.CODE', $deliveryCode)->setCacheTtl(360000)->cacheJoins(true)->exec();
        if ($service = $result->fetch()) {
            return (new \Bitrix\Sale\Delivery\ExtraServices\Manager([$service]))->getItemByCode($serviceCode);
        }
        return null;
    }
}
