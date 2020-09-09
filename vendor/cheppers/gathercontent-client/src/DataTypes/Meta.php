<?php

namespace Cheppers\GatherContent\DataTypes;

class Meta extends Base
{
    /**
     * @var array
     */
    public $assets = [];

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
                'assets' => 'assets',
            ]
        );

        return $this;
    }
}
