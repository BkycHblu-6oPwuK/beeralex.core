<?php
namespace Beeralex\Core\Http\Resources;

interface ResourceContract
{
    public static function make(array $data) : static;
    /**
     * @return array<string, mixed>
     */
    public function toArray();
}