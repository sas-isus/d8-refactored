<?php

namespace Cheppers\GatherContent\DataTypes;

class Account extends Base
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $slug = '';

    /**
     * @var string
     */
    public $timezone = '';

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'name' => 'name',
                'slug' => 'slug',
                'timezone' => 'timezone',
            ]
        );

        return $this;
    }
}
