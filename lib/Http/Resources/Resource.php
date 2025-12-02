<?php
declare(strict_types=1);
namespace Beeralex\Core\Http\Resources;

use Beeralex\Core\Traits\Resourceble;

/**
 * Базовый абстрактный класс для ресурсов API и в целом DTO
 */
abstract class Resource implements ResourceContract, \JsonSerializable, \ArrayAccess, \Countable
{
    use Resourceble;

    public final function __construct(array $data)
    {
        $this->resource = $data;
    }

    public static function make(array $data) : static
    {
        return new static($data);
    }
}