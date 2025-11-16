<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\FuserTable;
use CSaleUser;

class FuserService
{
    public function __construct() 
    {
        Loader::includeModule('sale');
    }

    /**
     * Получает FUSER_ID из сессии пользователя или из cookies
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function getFuserIdFromSession(): int
    {
        // Логика взята из CAllSaleUser::GetID

        $id = (int)$_SESSION['SALE_USER_ID'];

        if ($id <= 0 && ($code = $_COOKIE[Option::get('main', 'cookie_name', 'BITRIX_SM') . '_SALE_UID'])) {
            if (Option::get('sale', 'encode_fuser_id', 'N') == 'Y' && strval($code) != '') {
                $res = CSaleUser::GetList(['CODE' => $code]);
                if(!empty($res)) {
                    $id = $res['ID'];
                }
            } elseif ((int)$code > 0) {
                $id = (int)$code;
            }
        }

        return $id;
    }

    /**
     * Получает ID пользователя по fuserId
     */
    public function getUserId(int $fuserId): ?int
    {
        return FuserTable::getRow([
            'select' => ['USER_ID'],
            'filter' => ['ID' => $fuserId]
        ])['USER_ID'];
    }

    /**
     * Получает fuserId для пользователя
     */
    public function getFuserIdForUser(int $userId): ?int
    {
        return FuserTable::getRow([
            'select' => ['ID'],
            'filter' => ['USER_ID' => $userId]
        ])['ID'];
    }

    /**
     * Создает fuserId для пользователя
     */
    public function addFuserForUser(int $userId): int
    {
        return FuserTable::add([
            'DATE_INSERT' => new DateTime(),
            'DATE_UPDATE' => new DateTime(),
            'USER_ID'      => $userId,
            'CODE'         => md5(time() . Random::getString(10, true)),
        ])->getId();
    }
}
