<?php

use Beeralex\Core\Service\ViteService;
use Beeralex\Core\Config\Config;
use Beeralex\Core\Config\ConfigLoaderFactory;
use Beeralex\Core\Config\Module\TabsFactory;
use Beeralex\Core\Http\Adapter\BitrixToPsrRequest;
use Beeralex\Core\Http\Adapter\BitrixToPsrResponse;
use Beeralex\Core\Http\Adapter\PsrToBitrixRequest;
use Beeralex\Core\Http\Adapter\PsrToBitrixResponse;
use Beeralex\Core\Http\HttpFactory;
use Beeralex\Core\Logger\FileLoggerFactory;
use Beeralex\Core\Logger\LoggerFactoryContract;
use Beeralex\Core\Model\SectionTableFactory;
use Beeralex\Core\Repository\LocationRepository;
use Beeralex\Core\Repository\PropertyFeaturesRepository;
use Beeralex\Core\Repository\PropertyRepository;
use Beeralex\Core\Service\Api\ClientService;
use Beeralex\Core\Service\CatalogService;
use Beeralex\Core\Service\ControllerService;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Service\FuserService;
use Beeralex\Core\Service\HlblockService;
use Beeralex\Core\Service\IblockService;
use Beeralex\Core\Service\LanguageService;
use Beeralex\Core\Service\LocationService;
use Beeralex\Core\Service\PaginationService;
use Beeralex\Core\Service\PathService;
use Beeralex\Core\Service\QueryService;
use Beeralex\Core\Service\UrlService;
use Beeralex\Core\Service\WebService;
use Beeralex\Core\Service\UserService;

return [
    'url_remove_parts' => [
        'value' => [
            'api',
            'v1',
        ]
    ],
    'services' => [
        'value' => [
            LoggerFactoryContract::class => [
                'constructor' => static function () {
                    return new FileLoggerFactory(
                        baseDir: $_SERVER['DOCUMENT_ROOT'] . '/local/logs'
                    );
                },
            ],
            ConfigLoaderFactory::class => [
                'className' => ConfigLoaderFactory::class
            ],
            TabsFactory::class => [
                'className' => TabsFactory::class
            ],
            SectionTableFactory::class => [
                'className' => SectionTableFactory::class
            ],
            HttpFactory::class => [
                'className' => HttpFactory::class
            ],
            LocationRepository::class => [
                'className' => LocationRepository::class
            ],
            PropertyFeaturesRepository::class => [
                'className' => PropertyFeaturesRepository::class
            ],
            PropertyRepository::class => [
                'className' => PropertyRepository::class
            ],
            CatalogService::class => [
                'className' => CatalogService::class
            ],
            ControllerService::class => [
                'className' => ControllerService::class
            ],
            FileService::class => [
                'className' => FileService::class
            ],
            FuserService::class => [
                'className' => FuserService::class
            ],
            QueryService::class => [
                'className' => QueryService::class
            ],
            HlblockService::class => [
                'className' => HlblockService::class
            ],
            IblockService::class => [
                'className' => IblockService::class
            ],
            LanguageService::class => [
                'className' => LanguageService::class
            ],
            LocationService::class => [
                'constructor' => static function () {
                    return new LocationService(
                        locationRepository: service(LocationRepository::class)
                    );
                }
            ],
            PaginationService::class => [
                'className' => PaginationService::class
            ],
            PathService::class => [
                'className' => PathService::class
            ],
            Config::class => [
                'className' => Config::class
            ],
            QueryService::class => [
                'className' => QueryService::class
            ],
            UserService::class => [
                'className' => UserService::class
            ],
            ViteService::class => [
                'constructor' => static function () {
                    return new ViteService(
                        config: service(Config::class)
                    );
                }
            ],
            WebService::class => [
                'className' => WebService::class
            ],
            ClientService::class => [
                'className' => ClientService::class
            ],
            UrlService::class => [
                'className' => UrlService::class
            ],
            BitrixToPsrRequest::class => [
                'constructor' => static function () {
                    return new BitrixToPsrRequest(
                        webService: service(WebService::class)
                    );
                }
            ],
            BitrixToPsrResponse::class => [
                'constructor' => static function () {
                    return new BitrixToPsrResponse(
                        webService: service(WebService::class)
                    );
                }
            ],
            PsrToBitrixRequest::class => [
                'className' => PsrToBitrixRequest::class
            ],
            PsrToBitrixResponse::class => [
                'className' => PsrToBitrixResponse::class
            ],
        ],
    ],
];
