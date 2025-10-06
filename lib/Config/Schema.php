<?php
namespace Beeralex\Core\Config;

final class Schema
{
    private array $tabs = [];

    public static function make(): self
    {
        return new self();
    }

    public function tab(string $id, string $title, string $description, callable $callback): self
    {
        $tab = new SchemaTab($id, $title, $description);
        $callback($tab);
        $this->tabs[] = $tab;
        return $this;
    }

    public function toArray(): array
    {
        return array_map(fn($tab) => $tab->toArray(), $this->tabs);
    }
}
