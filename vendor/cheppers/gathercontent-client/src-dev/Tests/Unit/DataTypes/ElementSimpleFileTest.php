<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementSimpleFile;

/**
 * @group GatherContentClient
 */
class ElementSimpleFileTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementSimpleFile::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'fileId' => 10,
            'filename' => 'filename.jpg',
            'mimeType' => 'image/jpeg',
            'url' => 'http://some.url/image.jpeg',
            'optimisedImageUrl' => 'http://some.url/image_opt.jpeg',
            'size' => 452,
        ];
        $cases['basic'][1] = [
            'file_id' => 10,
            'filename' => 'filename.jpg',
            'mime_type' => 'image/jpeg',
            'url' => 'http://some.url/image.jpeg',
            'optimised_image_url' => 'http://some.url/image_opt.jpeg',
            'size' => 452,
        ];

        return $cases;
    }
}
