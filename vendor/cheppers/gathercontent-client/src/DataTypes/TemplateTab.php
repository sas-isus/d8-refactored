<?php

namespace Cheppers\GatherContent\DataTypes;

class TemplateTab extends Base
{
    protected static $type2Class = [
        'text' => ElementText::class,
        'files' => Element::class,
        'section' => ElementSection::class,
        'choice_checkbox' => ElementCheckbox::class,
        'choice_radio' => ElementRadio::class,
    ];

    /**
     * @var string
     */
    public $label = '';

    /**
     * @var bool
     */
    public $hidden = false;

    /**
     * @var \Cheppers\GatherContent\DataTypes\Element[]
     */
    public $elements = [];

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
                'name' => 'id',
                'label' => 'label',
                'hidden' => 'hidden',
                'elements' => [
                    'type' => 'closure',
                    'closure' => function (array $data) {
                        $elements = [];
                        foreach ($data as $elementData) {
                            $class = static::$type2Class[$elementData['type']];
                            /** @var \Cheppers\GatherContent\DataTypes\Base $element */
                            $element = new $class($elementData);
                            $elements[$element->id] = $element;
                        }

                        return $elements;
                    },
                ],
            ]
        );

        return $this;
    }
}
