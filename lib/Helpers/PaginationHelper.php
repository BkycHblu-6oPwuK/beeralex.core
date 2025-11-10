<?php

namespace Beeralex\Core\Helpers;

class PaginationHelper
{
    private function __construct() {}
    
    public static function toArray(?\CIBlockResult $nav, int $pageWindow = 5): array
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
            'pages' => static::getPages($nav->NavPageNomer, $nav->NavPageCount, $pageWindow),
            'pageSize' => (int)$nav->NavPageSize,
            'currentPage' => (int)$nav->NavPageNomer,
            'pageCount' => (int)$nav->NavPageCount,
            'paginationUrlParam' => 'PAGEN_' . $nav->NavNum,
        ];
    }

    public static function getPages(int $currentPage, int $pageCount, int $pageWindow = 5): array
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

    public static function getPagination(int $itemsCnt, int $pageSize, string $pageUrlParam = 'page'): array
    {
        $pageCount = max(1, ceil($itemsCnt / $pageSize));
        $requestPage = (int)\Bitrix\Main\Context::getCurrent()->getRequest()->get($pageUrlParam);
        $currentPage = $requestPage ? min($requestPage, $pageCount) : 1;

        return [
            'pages'              => static::getPages($currentPage, $pageCount),
            'pageSize'           => $pageSize,
            'currentPage'        => $currentPage,
            'pageCount'          => $pageCount,
            'paginationUrlParam' => $pageUrlParam,
        ];
    }
}
