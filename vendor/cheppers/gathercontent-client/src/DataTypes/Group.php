<?php

namespace Cheppers\GatherContent\DataTypes;

class Group extends Base
{
    protected static $type2Class = [
        'text' => ElementText::class,
        'attachment' => Element::class,
        'guidelines' => ElementGuideline::class,
        'choice_checkbox' => ElementCheckbox::class,
        'choice_radio' => ElementRadio::class,
    ];

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var \Cheppers\GatherContent\DataTypes\Element[]
     */
    public $fields = [];

    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id'];

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'uuid' => 'id',
                'name' => 'name',
                'fields' => [
                    'type' => 'closure',
                    'closure' => function (array $data) {
                        $elements = [];
                        foreach ($data as $elementData) {
                            $class = static::$type2Class[$elementData['field_type']];
                            /** @var \Cheppers\GatherContent\DataTypes\Base $element */
                            $element = new $class($elementData);
                            $elements[] = $element;
                        }

                        return $elements;
                    },
                ],
            ]
        );

        return $this;
    }
}
