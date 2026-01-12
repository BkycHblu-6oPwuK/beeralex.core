<?php

declare(strict_types=1);

namespace Beeralex\Core\Service;

use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Query;

class FileService
{
    /**
     * Возвращает массив файлов в формате удобном для сохранения через CFile::SaveFile
     * @param array $files $_FILES
     */
    public function getFormattedToSafe(?array $files): array
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

    /**
     * @return array<int, string> Возвращает массив путей файлов по их ID
     */
    public function getPathByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $paths = [];
        $res = FileTable::getList([
            'filter' => ['@ID' => $ids],
            'select' => ['ID', 'SUBDIR', 'FILE_NAME'],
        ]);
        while ($file = $res->fetch()) {
            $paths[(int)$file['ID']] = '/upload/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
        }
        return $paths;
    }

    /**
     * Добавляет в запрос выборку пути картинки по полю ссылки на файл. IMG - алиас для файла, PICTURE_SRC - алиас для пути картинки.
     * не подходит для множественных файлов
     */
    public function addPictireSrcInQuery(Query $query, string $thisFieldReference): Query
    {
        $query->registerRuntimeField('IMG', [
            'data_type' => FileTable::class,
            'reference' => [
                "=this.{$thisFieldReference}" => 'ref.ID',
            ],
            'join_type' => 'LEFT'
        ])
            ->registerRuntimeField("PICTURE_SRC", new ExpressionField(
                "PICTURE_SRC",
                'CONCAT("/upload/", %s, "/", %s)',
                ["IMG.SUBDIR", "IMG.FILE_NAME"]
            ));
        return $query;
    }

    /**
     * Рекурсивно копирует файлы из одной директории в другую
     */
    public function copyRecursive(string $source, string $target)
    {
        if (!is_dir($source)) {
            return;
        }
        $dir = opendir($source);
        @mkdir($target, 0775, true);

        while (false !== ($file = readdir($dir))) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $srcPath = $source . '/' . $file;
            $dstPath = $target . '/' . $file;

            if (is_dir($srcPath)) {
                $this->copyRecursive($srcPath, $dstPath);
            } else {
                @mkdir(dirname($dstPath), 0775, true);

                if (!file_exists($dstPath)) {
                    copy($srcPath, $dstPath);
                }
            }
        }

        closedir($dir);
    }

    /**
     * @param string $path относительно $basePath
     * 
     ```php
        public function fooAction()
		{
			FilesHelper::includeFile('catalog.index')
		}
     ```
     */
    public function includeFile(string $path, array $params = [], string $basePath = '/include/'): void
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . $basePath . str_replace('.', '/', $path) . '.php';
        if (!file_exists($file)) {
            return;
        }

        extract($params, EXTR_SKIP);
        include $file;
    }
}
