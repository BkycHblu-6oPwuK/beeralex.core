<?php

namespace Beeralex\Core\Enum;

enum Locations
{
    /** Страна */
    case COUNTRY;

    /** Округ */
    case COUNTRY_DISTRICT;

    /** Регион */
    case REGION;

    /** Субрегион */
    case SUBREGION;

    /** Город */
    case CITY;

    /** Деревня */
    case VILLAGE;

    /** Улица */
    case STREET;
}
