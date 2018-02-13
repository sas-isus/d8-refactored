<?php

namespace Cheppers\GatherContent\DataTypes;

class Project extends Base
{
    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $type = '';

    /**
     * @var bool
     */
    public $example = false;

    /**
     * @var int
     */
    public $accountId = 0;

    /**
     * @var bool
     */
    public $active = true;

    /**
     * @var string
     */
    public $textDirection = '';

    /**
     * @var array
     */
    public $allowedTags = [];

    /**
     * @var int
     */
    public $createdAt = 0;

    /**
     * @var int
     */
    public $updatedAt = 0;

    /**
     * @var bool
     */
    public $overdue = false;

    /**
     * @var \Cheppers\GatherContent\DataTypes\Status[]
     */
    public $statuses = [];

    /**
     * @var array
     */
    public $meta = [];

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'name' => 'name',
                'type' => 'type',
                'example' => 'example',
                'account_id' => 'accountId',
                'active' => 'active',
                'text_direction' => 'textDirection',
                'allowed_tags' => [
                    'type' => 'setJsonDecode',
                    'destination' => 'allowedTags',
                ],
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
                'overdue' => 'overdue',
                'statuses' => [
                    'type' => 'subConfigs',
                    'class' => Status::class,
                    'parents' => ['data'],
                ],
                'meta' => 'meta',
            ]
        );

        return $this;
    }
}
