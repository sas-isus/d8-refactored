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
    public $createdBy = 0;

    /**
     * @var int
     */
    public $updatedBy = 0;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var \Cheppers\GatherContent\DataTypes\TemplateTab[]
     */
    public $config = [];

    /**
     * @var string
     */
    public $usedAt = '';

    /**
     * @var int
     */
    public $createdAt = 0;

    /**
     * @var int
     */
    public $updatedAt = 0;

    /**
     * @var null|\Cheppers\GatherContent\DataTypes\Usage
     */
    public $usage = null;

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'project_id' => 'projectId',
                'created_by' => 'createdBy',
                'updated_by' => 'updatedBy',
                'name' => 'name',
                'description' => 'description',
                'config' => [
                    'type' => 'subConfigs',
                    'class' => TemplateTab::class,
                ],
                'used_at' => 'usedAt',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
                'usage' => [
                    'type' => 'subConfig',
                    'class' => Usage::class,
                ],
            ]
        );

        return $this;
    }
}
