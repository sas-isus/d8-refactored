<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementText;
use Cheppers\GatherContent\DataTypes\ElementTextMeta;

/**
 * @group GatherContentClient
 */
class ElementTextTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementText::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'id' => 'uuid-123',
            'type' => 'text',
            'label' => 'label string',
            'instructions' => 'instruction string',
            'metaData' => new ElementTextMeta([
                'is_plain' => true,
                'validation' => [],
            ]),
        ];
        $cases['basic'][1] = [
            'uuid' => 'uuid-123',
            'field_type' => 'text',
            'label' => 'label string',
            'instructions' => 'instruction string',
            'metadata' => [
                'is_plain' => true,
                'validation' => [],
            ],
        ];

        return $cases;
    }
}
