<?php
declare(strict_types=1);
namespace Beeralex\Core\Config\Module\Schema;

class SchemaTab
{
    protected array $fields = [];

    public function __construct(
        protected string $id,
        protected string $title,
        protected string $description
    ) {}

    public function checkbox(string $name, string $help, ?string $label = null, bool $disabled = false, bool $checked = false): static
    {
        $this->fields[] = [
            'type' => 'checkbox',
            'name' => $name,
            'help' => $help,
            'label' => $label,
            'disabled' => $disabled,
            'checked' => $checked,
        ];
        return $this;
    }

    public function input(string $name, string $help, ?string $label = null, int|string|array|null $size = null, bool $disabled = false, mixed $default = null): static
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

    public function password(string $name, string $help, ?string $label = null, mixed $default = null): static
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

    public function staticText(string $help, string $text): static
    {
        $this->fields[] = [
            'type' => 'staticText',
            'help' => $help,
            'text' => $text,
        ];
        return $this;
    }

    public function staticHtml(string $help, string $html): static
    {
        $this->fields[] = [
            'type' => 'staticHtml',
            'help' => $help,
            'html' => $html,
        ];
        return $this;
    }

    public function textArea(string $name, string $help, ?string $label = null, ?array $size = null, mixed $default = null): static
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

    public function select(string $name, string $help, array $options, ?string $label = null, bool $disabled = false, mixed $default = null): static
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

    public function multiSelect(string $name, string $help, array $options, ?string $label = null, mixed $default = null): static
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
