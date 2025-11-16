<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Query;

class FileService
{
    /**
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

    public function addPictireSrcInQuery(Query $query, string $thisFieldReference): Query
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
