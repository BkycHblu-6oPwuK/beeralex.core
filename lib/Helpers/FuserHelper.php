<?

namespace Beeralex\Core\Helpers;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Internals\FuserTable;
use CSaleUser;


Loader::includeModule('sale');
Loader::includeModule('iblock');


class FuserHelper
{
    private function __construct() {}
    /**
     * Получает FUSER_ID из сессии пользователя или из cookies
     *
     * @return int
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public static function getFuserIdFromSession(): int
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
     *
     * @param int $fuserId
     * @return int|null
     */
    public static function getUserId(int $fuserId): ?int
    {
        return FuserTable::getRow([
            'select' => ['USER_ID'],
            'filter' => ['ID' => $fuserId]
        ])['USER_ID'];
    }


    /**
     * Получает fuserId для пользователя
     *
     * @param int $userId
     * @return int|null
     */
    public static function getFuserIdForUser(int $userId): ?int
    {
        return FuserTable::getRow([
            'select' => ['ID'],
            'filter' => ['USER_ID' => $userId]
        ])['ID'];
    }



    /**
     * Создает fuserId для пользователя
     *
     * @param int $userId
     * @return int fuserId
     */
    public static function addFuserForUser(int $userId): int
    {
        return FuserTable::add([
            'DATE_INSERT' => new DateTime(),
            'DATE_UPDATE' => new DateTime(),
            'USER_ID'      => $userId,
            'CODE'         => md5(time() . randString(10)),
        ])->getId();
    }
}
