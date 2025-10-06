<?php

namespace Beeralex\Core\Config;

final class SchemaTab
{
    private array $fields = [];

    public function __construct(
        private string $id,
        private string $title,
        private string $description
    ) {}

    public function checkbox(string $name, string $help, ?string $label = null, bool $disabled = false, mixed $default = null): self
    {
        $this->fields[] = [
            'type' => 'checkbox',
            'name' => $name,
            'help' => $help,
            'label' => $label,
            'disabled' => $disabled,
            'default' => $default,
        ];
        return $this;
    }

    public function input(string $name, string $help, ?string $label = null, int|string|array|null $size = null, bool $disabled = false, mixed $default = null): self
    {
        $this->fields[] = [
            'type' => 'input',
            'name' => $name,
            'help' => $help,
            'label' => $label,
            'size' => $size,
            'disabled' => $disabled,
            'default' => $default,
        ];
        return $this;
    }

    public function password(string $name, string $help, ?string $label = null, mixed $default = null): self
    {
        $this->fields[] = [
            'type' => 'password',
            'name' => $name,
            'help' => $help,
            'label' => $label,
            'default' => $default,
        ];
        return $this;
    }

    public function staticText(string $help, string $text): self
    {
        $this->fields[] = [
            'type' => 'staticText',
            'help' => $help,
            'text' => $text,
        ];
        return $this;
    }

    public function staticHtml(string $help, string $html): self
    {
        $this->fields[] = [
            'type' => 'staticHtml',
            'help' => $help,
            'html' => $html,
        ];
        return $this;
    }

    public function textArea(string $name, string $help, ?string $label = null, ?array $size = null, mixed $default = null): self
    {
        $this->fields[] = [
            'type' => 'textArea',
            'name' => $name,
            'help' => $help,
            'label' => $label,
            'size' => $size,
            'default' => $default,
        ];
        return $this;
    }

    public function select(string $name, string $help, array $options, ?string $label = null, bool $disabled = false, mixed $default = null): self
    {
        $this->fields[] = [
            'type' => 'select',
            'name' => $name,
            'help' => $help,
            'label' => $label,
            'options' => $options,
            'disabled' => $disabled,
            'default' => $default,
        ];
        return $this;
    }

    public function multiSelect(string $name, string $help, array $options, ?string $label = null, mixed $default = null): self
    {
        $this->fields[] = [
            'type' => 'multiSelect',
            'name' => $name,
            'help' => $help,
            'label' => $label,
            'options' => $options,
            'default' => $default,
        ];
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'fields' => $this->fields,
        ];
    }
}
