<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementRadio extends ElementChoice
{
    /**
     * @var bool
     */
    public $otherOption = false;

    /**
     * {@inheritdoc}
     */
    public $type = 'choice_radio';

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'other_option' => 'otherOption',
            ]
        );

        return $this;
    }
}
