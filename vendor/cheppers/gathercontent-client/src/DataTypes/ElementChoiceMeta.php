<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementChoiceMeta extends Base
{
    /**
     * @var array
     */
    public $choiceFields = [];

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
                'choice_fields' => 'choiceFields',
            ]
        );

        return $this;
    }
}
