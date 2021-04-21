<?php

namespace Cheppers\GatherContent\DataTypes;

class Usage extends Base
{
    /**
     * @var int
     */
    public $itemCount = 0;

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
                'item_count' => 'itemCount',
            ]
        );

        return $this;
    }
}
