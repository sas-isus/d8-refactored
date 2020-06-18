<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementText extends Element
{
    /**
     * {@inheritdoc}
     */
    public $type = 'text';

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'metadata' => [
                    'type' => 'subConfig',
                    'class' => ElementTextMeta::class,
                    'destination' => 'metaData',
                ],
            ]
        );

        return $this;
    }
}
