<?php
namespace Beeralex\Core\Config;

use InvalidArgumentException;
use Beeralex\Core\Config\Fields\Field;

class Tab
{
    private $id;
    private $name;
    private $title;
    /**
     * @var Field[]
     */
    private $fields;

    private $addedNames;

    public function __construct(string $id, string $name, string $title)
    {
        $this->id = $id;
        $this->name = $name;
        $this->title = $title;
        $this->fields = [];
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

    public function getOptionsFormattedArray()
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
