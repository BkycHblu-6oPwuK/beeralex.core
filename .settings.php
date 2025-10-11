<?php

use Beeralex\Core\Logger\FileLoggerFactory;
use Beeralex\Core\Logger\LoggerFactoryContract;

return [
    'services' => [
        'value' => [
            LoggerFactoryContract::class => [
                'constructor' => static function () {
                    return new FileLoggerFactory($_SERVER['DOCUMENT_ROOT'] . '/local/logs');
                },
            ],
        ],
    ],
];
