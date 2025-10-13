<?php
namespace Beeralex\Core\Http\Resources;

interface ResourceContract
{
    public static function make(array $data) : static;
    public function toArray() : array;
}