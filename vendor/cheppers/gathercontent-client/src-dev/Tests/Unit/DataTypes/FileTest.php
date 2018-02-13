<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\File;

/**
 * @group GatherContentClient
 */
class FileTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = File::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] += [
            'userId' => 1,
            'itemId' => 2,
            'field' => 'a',
            'url' => 'b',
            'fileName' => 'c',
            'size' => 3,
            'createdAt' => 4,
            'updatedAt' => 5,
            'type' => 'd',
        ];
        $cases['basic'][1] += [
            'user_id' => 1,
            'item_id' => 2,
            'field' => 'a',
            'url' => 'b',
            'filename' => 'c',
            'size' => 3,
            'created_at' => 4,
            'updated_at' => 5,
            'type' => 'd',
        ];

        return $cases;
    }
}
