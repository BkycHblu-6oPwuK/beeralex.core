<?php
declare(strict_types=1);
namespace Beeralex\Core\Config\Module;

use InvalidArgumentException;
use Beeralex\Core\Config\Module\Fields\Field;

class Tab
{
    protected string $id;
    protected string $name;
    protected string $title;
    /**
     * @var Field[]
     */
    protected array $fields = [];

    protected array $addedNames = [];

    public function __construct(string $id, string $name, string $title)
    {
        $this->id = $id;
        $this->name = $name;
        $this->title = $title;
    }

    public function addField(Field $field)
    {
        $name = $field->getName();
        
        if($name){
            if($this->addedNames[$name]){
                throw new InvalidArgumentException("Поле с таким именем ($name) уже было добавлено");
            }
            $this->addedNames[$name] = true;
        }

        $this->fields[] = $field;
    }
    
    public function getFields()
    {
        return $this->fields;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getOptionsFormattedArray(): array
    {
        $options = [];
        foreach ($this->fields as $field) {
            if($label = $field->getLabel()){
                $options[] = $label;
            }
            $options[] = $field->getOptions();
        }
        return $options;
    }
}
