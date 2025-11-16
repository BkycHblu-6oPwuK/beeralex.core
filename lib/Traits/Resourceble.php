<?php
declare(strict_types=1);
namespace Beeralex\Core\Traits;

trait Resourceble
{
    private $resource;

    public function __get(string $property): mixed
    {
        if ($property === 'resource') {
            return $this->resource;
        }
        return $this->resource[$property] ?? null;
    }

    public function __set(string $property, mixed $value): void
    {
        if ($property === 'resource') {
            $this->resource = $value;
            return;
        }
        $this->resource[$property] = $value;
    }

    public function __unset(string $property): void
    {
        unset($this->resource[$property]);
    }

    public function __isset(string $property): bool
    {
        return isset($this->resource[$property]);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->resource[] = $value;
        } else {
            $this->resource[$offset] = $value;
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->resource[$offset]);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->resource[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return isset($this->resource[$offset]) ? $this->resource[$offset] : null;
    }

    public function count(): int
    {
        return count($this->resource);
    }

    public function jsonSerialize(): mixed
    {
        return $this->resource;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->resource);
    }
}
