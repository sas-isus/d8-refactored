<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementSimpleChoice extends ElementBase
{
    /**
     * @var string
     */
    public $label = '';

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'label' => 'label',
            ]
        );

        return $this;
    }
}
