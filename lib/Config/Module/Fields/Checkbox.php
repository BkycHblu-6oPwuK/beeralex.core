<?
declare(strict_types=1);
namespace Beeralex\Core\Config\Module\Fields;

class Checkbox extends Field
{
    protected function getType() : string
    {
        return 'checkbox';
    }

    public function setDefaultValue(int|string|array $defaultValue)
    {
        throw new \InvalidArgumentException("Для чекбокса используйте метод isChecked()");
    }

    public function isChecked()
    {
        $this->defaultValue = 'Y';
        return $this;
    }
}
