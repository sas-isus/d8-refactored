<?php

namespace Cheppers\GatherContent\DataTypes;

class Date extends Base
{
    /**
     * @var string
     */
    public $date = '';

    /**
     * @var int
     */
    public $timezoneType = 0;

    /**
     * @var string
     */
    public $timezone = '';

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
                'date' => 'date',
                'timezone_type' => 'timezoneType',
                'timezone' => 'timezone',
            ]
        );

        return $this;
    }
}
