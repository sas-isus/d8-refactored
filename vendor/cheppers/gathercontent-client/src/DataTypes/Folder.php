<?php

namespace Cheppers\GatherContent\DataTypes;

class Folder extends Base
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $position = '';

    /**
     * @var string
     */
    public $parentUuid = '';

    /**
     * @var int
     */
    public $projectId = 0;

    /**
     * @var string
     */
    public $type = '';

    /**
     * @var int
     */
    public $archivedAt = 0;

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
                'name' => 'name',
                'position' => 'position',
                'parent_uuid' => 'parentUuid',
                'project_id' => 'projectId',
                'type' => 'type',
                'archived_at' => 'archivedAt',
            ]
        );

        return $this;
    }
}
