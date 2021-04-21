<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementFiles extends Element
{
    /**
     * {@inheritdoc}
     */
    public $type = 'files';

    protected $unusedProperties = [
        'id',
    ];

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        return $this;
    }

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        return $this;
    }
}
