<?php

namespace Cheppers\GatherContent\DataTypes;

class Item extends Base
{
    /**
     * @var int
     */
    public $projectId = 0;

    /**
     * @var string
     */
    public $folderUuid = '';

    /**
     * @var int
     */
    public $templateId = 0;

    /**
     * @var string
     */
    public $structureUuid = '';

    /**
     * @var \Cheppers\GatherContent\DataTypes\Structure
     */
    public $structure = null;

    /**
     * @var string
     */
    public $position = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var null|int
     */
    public $archivedBy = null;

    /**
     * @var string
     */
    public $archivedAt = '';

    /**
     * @var string
     */
    public $createdAt = '';

    /**
     * @var string
     */
    public $updatedAt = '';

    /**
     * @var string
     */
    public $nextDueAt = '';

    /**
     * @var string
     */
    public $completedAt = '';

    /**
     * @var \Cheppers\GatherContent\DataTypes\ElementBase[]
     */
    public $content = [];

    /**
     * @var null|int
     */
    public $statusId = null;

    /**
     * @var array
     */
    public $assignedUserIds = [];

    /**
     * @var null|int
     */
    public $assigneeCount = null;

    /**
     * @var null|int
     */
    public $approvalCount = null;

    /**
     * @var array
     */
    public $assets = [];

    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'project_id' => 'projectId',
                'folder_uuid' => 'folderUuid',
                'template_id' => 'templateId',
                'structure' => [
                    'type' => 'subConfig',
                    'class' => Structure::class,
                ],
                'structure_uuid' => 'structureUuid',
                'position' => 'position',
                'name' => 'name',
                'archived_by' => 'archivedBy',
                'archived_at' => 'archivedAt',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
                'next_due_at' => 'nextDueAt',
                'completed_at' => 'completedAt',
                'status_id' => 'statusId',
                'assigned_user_ids' => 'assignedUserIds',
                'assignee_count' => 'assigneeCount',
                'approval_count' => 'approvalCount',
                'content' => [
                    'type' => 'closure',
                    'closure' => function (array $data) {
                        $elements = [];
                        foreach ($data as $key => $elementData) {
                            if (!is_array($elementData)) {
                                $elements[$key] = new ElementSimpleText(['value' => $elementData]);
                                continue;
                            }

                            $elements[$key] = $this->getSubElements($elementData);
                        }

                        return $elements;
                    },
                ],
                'assets' => 'assets',
            ]
        );

        return $this;
    }

    /**
     * Return sub element type.
     *
     * @param  array  $elementData
     * @return array|ElementBase[]
     */
    protected function getSubElements(array $elementData)
    {
        $elements = [];

        foreach ($elementData as $element) {
            if (empty($element)) {
                continue;
            }
            $class = ElementSimpleChoice::class;
            if (isset($element['file_id'])) {
                $class = ElementSimpleFile::class;
            }
            if (!is_array($element)) {
                $class = ElementSimpleText::class;
                $element = ['value' => $element];
            }
            /** @var \Cheppers\GatherContent\DataTypes\ElementBase[] $elements */
            $elements[] = new $class($element);
        }

        return $elements;
    }
}
