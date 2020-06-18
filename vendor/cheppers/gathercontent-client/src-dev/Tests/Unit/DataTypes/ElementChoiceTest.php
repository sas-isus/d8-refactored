<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementChoice;
use Cheppers\GatherContent\DataTypes\ElementChoiceMeta;

/**
 * @group GatherContentClient
 */
class ElementChoiceTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementChoice::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'id' => 'uuid-123',
            'label' => 'label string',
            'instructions' => 'instruction string',
            'metaData' => new ElementChoiceMeta([
                'choice_fields' => [
                    'options' => [
                        [
                        'optionId' => 'option id 1',
                        'label' => 'option label 1',
                        ],
                        [
                        'optionId' => 'option id 2',
                        'label' => 'option label 2',
                        ],
                    ],
                ],
            ]),
        ];
        $cases['basic'][1] = [
            'id' => 'uuid-123',
            'label' => 'label string',
            'instructions' => 'instruction string',
            'metadata' => [
                'choice_fields' => [
                    'options' => [
                        [
                            'optionId' => 'option id 1',
                            'label' => 'option label 1',
                        ],
                        [
                            'optionId' => 'option id 2',
                            'label' => 'option label 2',
                        ],
                    ],
                ],
            ],
        ];

        return $cases;
    }
}
