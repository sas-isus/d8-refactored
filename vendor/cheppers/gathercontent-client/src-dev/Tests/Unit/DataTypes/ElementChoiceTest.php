<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementChoice;

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
            'options' => [
                'a' => [
                    'b' => 'c',
                    'd' => 'e',
                ],
            ],
        ];
        $cases['basic'][1] = [
            'options' => [
                'a' => [
                    'b' => 'c',
                    'd' => 'e',
                ],
            ],
        ];

        return $cases;
    }

    public function testGetSetValue()
    {
        /** @var \Cheppers\GatherContent\DataTypes\ElementChoice $element */
        $element = new $this->className([]);

        static::assertEquals([], $element->options);
        static::assertEquals([], $element->getValue());

        $element->setValue([
            'op_1' => true,
            'op_2' => false,
            'op_3' => true,
        ]);
        static::assertEquals([], $element->options);
        static::assertEquals([], $element->getValue());


        $element->options = [
            'op_1' => [
                'name' => 'op_1',
                'label' => 'OP 1',
                'selected' => true,
            ],
            'op_2' => [
                'name' => 'op_2',
                'label' => 'OP 2',
                'selected' => false,
            ],
            'op_3' => [
                'name' => 'op_3',
                'label' => 'OP 3',
                'selected' => true,
            ],
        ];

        static::assertEquals(
            [
                'op_1' => true,
                'op_2' => false,
                'op_3' => true,
            ],
            $element->getValue()
        );

        $element->setValue([
            'op_2' => true,
            'op_3' => false,
        ]);
        static::assertEquals(
            [
                'op_1' => true,
                'op_2' => true,
                'op_3' => false,
            ],
            $element->getValue()
        );

        static::assertEquals(
            [
                'op_1' => 'OP 1',
                'op_2' => 'OP 2',
                'op_3' => 'OP 3',
            ],
            $element->getOptions()
        );
    }
}
