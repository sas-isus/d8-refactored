<?php

namespace Cheppers\GatherContent\DataTypes;

class File extends Base
{
    /**
     * @var int
     */
    public $userId = 0;

    /**
     * @var int
     */
    public $itemId = 0;

    /**
     * @var string
     */
    public $field = '';

    /**
     * @var string
     */
    public $type = '';

    /**
     * @var string
     */
    public $url = '';

    /**
     * Original file name.
     *
     * @var string
     */
    public $fileName = '';

    /**
     * @var int
     */
    public $size = 0;

    /**
     * @var int
     */
    public $createdAt = 0;

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
                'user_id' => 'userId',
                'item_id' => 'itemId',
                'field' => 'field',
                'type' => 'type',
                'url' => 'url',
                'filename' => 'fileName',
                'size' => 'size',
                'created_at' => 'createdAt',
                'updated_at' => 'updatedAt',
            ]
        );

        return $this;
    }
}
