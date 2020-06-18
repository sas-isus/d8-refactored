<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementTextMeta extends Base
{
    /**
     * @var bool
     */
    public $isPlain = false;

    /**
     * @var array
     */
    public $validation = [];

    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id'];

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'is_plain' => 'isPlain',
                'validation' => 'validation',
            ]
        );

        return $this;
    }
}
