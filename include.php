<?php

use Beeralex\Core\Config\ConfigLoaderFactory;

require_once __DIR__ . '/lib/functions.php';

$providers = ConfigLoaderFactory::getArrayLoader()->tryLoad('providers.php');
foreach($providers as $provider) {
    (new $provider)->register();
}