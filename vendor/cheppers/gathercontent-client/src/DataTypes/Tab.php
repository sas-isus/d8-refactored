<?php

namespace Cheppers\GatherContent\DataTypes;

class Tab extends Base
{
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

    public function jsonSerialize()
    {
        $values = parent::jsonSerialize();
        $values['elements'] = array_values($values['elements']);

        return $values;
    }

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
                            $class = Element::$type2Class[$elementData['type']];
                            /** @var \Cheppers\GatherContent\DataTypes\Element $element */
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
