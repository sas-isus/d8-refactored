<?php

namespace Cheppers\GatherContent\DataTypes;

class Item extends Base
{
    /**
     * @var int
     */
    public $projectId = 0;

    /**
     * @var int
     */
    public $parentId = 0;

    /**
     * @var int
     */
    public $templateId = 0;

    /**
     * @var int
     */
    public $customStateId = 0;

    /**
     * @var string
     */
    public $position = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var \Cheppers\GatherContent\DataTypes\Tab[]
     */
    public $config = [];

    /**
     * @var string
     */
    public $notes = '';

    /**
     * @var bool
     */
    public $overdue = false;

    /**
     * @var null|int
     */
    public $archivedBy = null;

    /**
     * @var null|\Cheppers\GatherContent\DataTypes\Date
     */
    public $archivedAt = null;

    /**
     * @var \Cheppers\GatherContent\DataTypes\Date
     */
    public $createdAt = null;

    /**
     * @var null|\Cheppers\GatherContent\DataTypes\Date
     */
    public $updatedAt = null;

    /**
     * @var \Cheppers\GatherContent\DataTypes\Status
     */
    public $status = null;

    /**
     * @var \Cheppers\GatherContent\DataTypes\Date[]
     */
    public $dueDates = [];

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'project_id' => 'projectId',
                'parent_id' => 'parentId',
                'template_id' => 'templateId',
                'custom_state_id' => 'customStateId',
                'position' => 'position',
                'name' => 'name',
                'config' => [
                    'type' => 'subConfigs',
                    'class' => Tab::class,
                ],
                'notes' => 'notes',
                'type' => 'item',
                'overdue' => 'overdue',
                'archived_by' => 'archivedBy',
                'archived_at' => 'archivedAt',
                'created_at' => [
                    'type' => 'subConfig',
                    'destination' => 'createdAt',
                    'class' => Date::class,
                ],
                'updated_at' => [
                    'type' => 'subConfig',
                    'destination' => 'updatedAt',
                    'class' => Date::class,
                ],
                'status' => [
                    'type' => 'subConfig',
                    'class' => Status::class,
                    'parents' => ['data'],
                ],
                'due_dates' => [
                    'type' => 'subConfigs',
                    'destination' => 'dueDates',
                    'class' => Date::class,
                    'parents' => ['data'],
                ],
            ]
        );

        return $this;
    }
}
