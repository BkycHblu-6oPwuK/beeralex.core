<?php
declare(strict_types=1);
namespace Beeralex\Core\Config\Module\Schema;

class Schema
{
    protected array $tabs = [];

    public static function make(): static
    {
        return new static();
    }

    /**
     * @param ?callable $callback function(SchemaTab $tab): void
     */
    public function tab(string $id, string $title, string $description, ?callable $callback = null): static
    {
        $tab = new SchemaTab($id, $title, $description);
        if ($callback) {
            $callback($tab);
        }
        $this->tabs[] = $tab;
        return $this;
    }

    public function toArray(): array
    {
        return array_map(fn($tab) => $tab->toArray(), $this->tabs);
    }
}
