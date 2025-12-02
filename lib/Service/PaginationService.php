<?php
declare(strict_types=1);
namespace Beeralex\Core\Service;

use Bitrix\Main\Loader;

class PaginationService
{
    public function __construct() 
    {
        Loader::includeModule("iblock");
    }
    
    /**
     * Преобразует объект навигации CIBlockResult в массив с данными пагинации
     */
    public function toArray(?\CIBlockResult $nav, int $pageWindow = 5): array
    {
        if (!$nav) {
            return [
                'pages'              => [],
                'pageSize'           => 0,
                'currentPage'        => 1,
                'pageCount'          => 0,
                'paginationUrlParam' => '',
            ];
        }

        return [
            'pages' => $this->getPages((int)$nav->NavPageNomer, (int)$nav->NavPageCount, $pageWindow),
            'pageSize' => (int)$nav->NavPageSize,
            'currentPage' => (int)$nav->NavPageNomer,
            'pageCount' => (int)$nav->NavPageCount,
            'paginationUrlParam' => 'PAGEN_' . $nav->NavNum,
        ];
    }

    /**
     * Возвращает массив страниц для пагинации
     */
    public function getPages(int $currentPage, int $pageCount, int $pageWindow = 5): array
    {
        if ($currentPage > floor($pageWindow / 2) + 1 && $pageCount > $pageWindow) {
            $startPage = $currentPage - floor($pageWindow / 2);
        } else {
            $startPage = 1;
        }

        if ($currentPage <= $pageCount - floor($pageWindow / 2) && $startPage + $pageWindow - 1 <= $pageCount) {
            $endPage = $startPage + $pageWindow - 1;
        } else {
            $endPage = $pageCount;
            if ($endPage - $pageWindow + 1 >= 1) {
                $startPage = $endPage - $pageWindow + 1;
            }
        }

        $pages = [];
        for ($i = $startPage; $i <= $endPage; $i++) {
            $pages[] = [
                'pageNumber' => $i,
                'isSelected' => (int)$currentPage == $i,
            ];
        }
        return $pages;
    }

    /**
     * Возвращает параметры пагинации по количеству элементов и размеру страницы
     */
    public function getPagination(int $itemsCnt, int $pageSize, string $pageUrlParam = 'page'): array
    {
        $pageCount = (int)max(1, ceil($itemsCnt / $pageSize));
        $requestPage = (int)\Bitrix\Main\Context::getCurrent()->getRequest()->get($pageUrlParam);
        $currentPage = $requestPage ? min($requestPage, $pageCount) : 1;

        return [
            'pages'              => $this->getPages($currentPage, $pageCount),
            'pageSize'           => $pageSize,
            'currentPage'        => $currentPage,
            'pageCount'          => $pageCount,
            'paginationUrlParam' => $pageUrlParam,
        ];
    }
}
