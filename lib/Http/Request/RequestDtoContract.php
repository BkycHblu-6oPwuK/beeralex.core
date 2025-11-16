<?php
namespace Beeralex\Core\Http\Request;

use Bitrix\Main\Validation\ValidationResult;

interface RequestDtoContract
{
    public static function fromArray(array $data): static;
    public function getData(): array;
    public function isValid(): bool;
    public function getErrors(): array;
    public function setValidationResult(ValidationResult $result): static;
}
