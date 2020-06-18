<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementSimpleText extends ElementBase
{
    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = [
        'id',
    ];

    /**
     * @var string
     */
    public $value = '';

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'value' => 'value',
            ]
        );

        return $this;
    }

    /**
     * Need this custom serializer so we can receive back the same json what the API sends.
     */
    public function jsonSerialize()
    {
        return $this->value;
    }
}
