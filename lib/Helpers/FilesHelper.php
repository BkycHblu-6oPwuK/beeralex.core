<?php

namespace Beeralex\Core\Helpers;

use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Query;

class FilesHelper
{
    private function __construct() {}
    /**
     * @param array $files $_FILES
     */
    public static function getFormattedToSafe(?array $files): array
    {
        if (empty($files)) {
            return [];
        }
        $toSavefiles = [];
        $diff = count($files) - count($files, COUNT_RECURSIVE);
        if ($diff == 0) {
            $toSavefiles = [$files];
        } else {
            foreach ($files as $k => $l) {
                foreach ($l as $i => $v) {
                    $toSavefiles[$i][$k] = $v;
                }
            }
        }
        return $toSavefiles;
    }

    public static function addPictireSrcInQuery(Query $query, string $thisFieldReference): Query
    {
        $query->registerRuntimeField('IMG', [
            'data_type' => FileTable::class,
            'reference' => [
                "=this.{$thisFieldReference}" => 'ref.ID',
            ],
            'join_type' => 'INNER'
        ])
            ->registerRuntimeField('PICTURE_SRC', new ExpressionField(
                'PICTURE_SRC',
                'CONCAT("/upload/", %s, "/", %s)',
                ['img.SUBDIR', 'img.FILE_NAME']
            ));
        return $query;
    }
}
