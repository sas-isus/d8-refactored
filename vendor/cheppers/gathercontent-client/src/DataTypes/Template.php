<?php

namespace Cheppers\GatherContent\DataTypes;

class Template extends Base
{
    /**
     * @var int
     */
    public $projectId = 0;

    /**
     * @var int
     */
    public $updatedBy = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var int
     */
    public $numberOfItemsUsing = 0;

    /**
     * @var string
     */
    public $structureUuid = '';

    /**
     * @var int
     */
    public $updatedAt = 0;

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'name' => 'name',
                'number_of_items_using' => 'numberOfItemsUsing',
                'structure_uuid' => 'structureUuid',
                'project_id' => 'projectId',
                'updated_at' => 'updatedAt',
                'updated_by' => 'updatedBy',
            ]
        );

        return $this;
    }
}
