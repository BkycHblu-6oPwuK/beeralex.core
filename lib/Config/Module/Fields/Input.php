<?
declare(strict_types=1);
namespace Beeralex\Core\Config\Module\Fields;

class Input extends Field
{
    public function setSize(string|int $size)
    {
        $this->extraOptions = $size;
        return $this;
    }

    protected function getType() : string
    {
        return 'text';
    }
}
