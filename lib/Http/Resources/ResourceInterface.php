<?php
namespace Beeralex\Core\Http\Resources;

interface ResourceInterface
{
    public static function make(array $data) : static;
    public function toArray() : array;
}