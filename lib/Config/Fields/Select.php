<?
namespace Beeralex\Core\Config\Fields;

class Select extends Field
{
    public function __construct(string $name, string $text, array $values)
    {
        parent::__construct($name, $text);
        $this->extraOptions = $values;
    }

    protected function getType() : string
    {
        return 'selectbox';
    }
}
