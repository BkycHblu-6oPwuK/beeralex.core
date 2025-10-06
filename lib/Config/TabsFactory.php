<?php
namespace Beeralex\Core\Config;

use Beeralex\Core\Config\Fields;

final class TabsFactory
{
    public static function fromSchema(array $schema): TabsBuilder
    {
        $builder = new TabsBuilder();

        foreach ($schema as $tabData) {
            $tab = new Tab($tabData['id'], $tabData['title'], $tabData['description']);

            foreach ($tabData['fields'] as $field) {
                switch ($field['type']) {
                    case 'checkbox':
                        $tab->addField(
                            (new Fields\Checkbox($field['name'], $field['help']))
                                ->setLabel($field['label'] ?? '')
                                ->setDefaultValue($field['default'] ?? '')
                                ->setDisabled($field['disabled'] ?? false)
                        );
                        break;

                    case 'input':
                        $tab->addField(
                            (new Fields\Input($field['name'], $field['help'], $field['size'] ?? null))
                                ->setLabel($field['label'] ?? '')
                                ->setDefaultValue($field['default'] ?? '')
                                ->setDisabled($field['disabled'] ?? false)
                        );
                        break;

                    case 'password':
                        $tab->addField(
                            (new Fields\Password($field['name'], $field['help']))
                                ->setLabel($field['label'] ?? '')
                                ->setDefaultValue($field['default'] ?? null)
                        );
                        break;

                    case 'staticText':
                        $tab->addField(
                            new Fields\StaticText($field['help'], $field['text'])
                        );
                        break;

                    case 'staticHtml':
                        $tab->addField(
                            new Fields\StaticHtml($field['help'], $field['html'])
                        );
                        break;

                    case 'textArea':
                        $tab->addField(
                            (new Fields\TextArea($field['name'], $field['help']))
                                ->setLabel($field['label'] ?? '')
                                ->setSize($field['size'] ?? [])
                                ->setDefaultValue($field['default'] ?? '')
                        );
                        break;

                    case 'select':
                        $tab->addField(
                            (new Fields\Select($field['name'], $field['help'], $field['options']))
                                ->setLabel($field['label'] ?? '')
                                ->setDefaultValue($field['default'] ?? '')
                                ->setDisabled($field['disabled'] ?? false)
                        );
                        break;

                    case 'multiSelect':
                        $tab->addField(
                            (new Fields\MultiSelect($field['name'], $field['help'], $field['options']))
                                ->setLabel($field['label'] ?? '')
                                ->setDefaultValue($field['default'] ?? '')
                        );
                        break;

                    default:
                        throw new \InvalidArgumentException("Unknown field type: {$field['type']}");
                }
            }

            $builder->addTab($tab);
        }

        return $builder;
    }
}
