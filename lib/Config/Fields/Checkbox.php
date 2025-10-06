<?
namespace Beeralex\Core\Config\Fields;

class Checkbox extends Field
{
    protected function getType() : string
    {
        return 'checkbox';
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue === true ? 'Y' : 'N';
        return $this;
    }

    public function isChecked()
    {
        $this->defaultValue = 'Y';
        return $this;
    }
}
