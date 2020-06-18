<?php

namespace Cheppers\GatherContent\DataTypes;

class Element extends Base
{
    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = ['id'];

    /**
     * @see https://docs.gathercontent.com/reference#tab-structure
     *
     * @var string
     */
    public $type = '';

    /**
     * @var string
     */
    public $label = '';

    /**
     * @var string
     */
    public $instructions = '';

    /**
     * @var array
     */
    public $metaData = [];

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'uuid' => 'id',
                'field_type' => 'type',
                'label' => 'label',
                'instructions' => 'instructions',
                'metadata' => 'metaData',
            ]
        );

        return $this;
    }
}
