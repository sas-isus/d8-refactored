<?php

namespace Cheppers\GatherContent\DataTypes;

class Announcement extends Base
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var bool
     */
    public $acknowledged = false;

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'name' => 'name',
                'acknowledged' => 'acknowledged',
            ]
        );

        return $this;
    }
}
