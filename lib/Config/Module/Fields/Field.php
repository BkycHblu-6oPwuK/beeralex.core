<?
declare(strict_types=1);
namespace Beeralex\Core\Config\Module\Fields;

abstract class Field
{
    protected string $name;
    protected ?string $label = null;
    protected string $text;
    protected bool $isDisabled = false;
    protected string|int|array|null $defaultValue = null;
    protected array|string|int|null $extraOptions = null;

    public function __construct(string $name, string $text)
    {
        $this->name = $name;
        $this->text = $text;
    }

    public final function getOptions()
    {
        return [$this->name, $this->text, $this->defaultValue, $this->getTypeAndExtraOptionsArray(), $this->getDisabled()];
    }

    public function setDefaultValue(int|string|array $defaultValue)
    {
        if(is_array($defaultValue)){
            $defaultValue = implode(',', $defaultValue);
        }
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function setText(string $text)
    {
        $this->text = $text;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setLabel(string $label)
    {
        $this->label = $label;
        return $this;
    }

    public function setDisabled(bool $isDisabled)
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    public function isDisabled()
    {
        return $this->setDisabled(true);
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    private function getDisabled()
    {
        return $this->isDisabled ? 'Y' : 'N';
    }

    protected function getTypeAndExtraOptionsArray() : array
    {
        return [$this->getType(), $this->getExtraOptions()];
    }

    public function getExtraOptions() : array|string|int|null
    {
        return $this->extraOptions;
    }

    abstract protected function getType() : string; // bitrix/modules/main/admin/settings.php функция renderInput отрисовывает поля по типам
}
