<?
namespace Beeralex\Core\Config\Fields;

class TextArea extends Field
{
    protected function getTypeAndExtraOptionsArray() : array
    {
        return array_merge([$this->getType()], $this->getExtraOptions());
    }

    /** @param array $size [$rows,$cols] */
    public function setSize(array $size)
    {
        $this->extraOptions = $size;
        return $this;
    }

    protected function getType() : string
    {
        return 'textarea';
    }
}