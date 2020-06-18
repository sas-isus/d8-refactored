<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementFiles;

/**
 * @group GatherContentClient
 */
class ElementFilesTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementFiles::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'type' => 'attachment'
        ];
        $cases['basic'][1] = [
            'field_type' => 'attachment'
        ];

        return $cases;
    }
}
