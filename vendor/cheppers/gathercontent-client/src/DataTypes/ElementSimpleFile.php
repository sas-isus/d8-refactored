<?php

namespace Cheppers\GatherContent\DataTypes;

class ElementSimpleFile extends ElementBase
{
    /**
     * {@inheritdoc}
     */
    protected $unusedProperties = [
        'id',
    ];

    /**
     * @var int|null
     */
    public $fileId = null;

    /**
     * @var string
     */
    public $filename = '';

    /**
     * @var string
     */
    public $mimeType = '';

    /**
     * @var string
     */
    public $url = '';

    /**
     * @var string
     */
    public $optimisedImageUrl = '';

    /**
     * @var int|null
     */
    public $size = null;

    /**
     * {@inheritdoc}
     */
    protected function initPropertyMapping()
    {
        parent::initPropertyMapping();
        $this->propertyMapping = array_replace(
            $this->propertyMapping,
            [
                'file_id' => 'fileId',
                'filename' => 'filename',
                'mime_type' => 'mimeType',
                'url' => 'url',
                'optimised_image_url' => 'optimisedImageUrl',
                'size' => 'size',
            ]
        );

        return $this;
    }
}
