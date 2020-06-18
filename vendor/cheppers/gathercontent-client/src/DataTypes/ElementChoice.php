<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementChoice extends Element
{
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
                    'class' => ElementChoiceMeta::class,
                    'destination' => 'metaData',
                ],
            ]
        );

        return $this;
    }
}
