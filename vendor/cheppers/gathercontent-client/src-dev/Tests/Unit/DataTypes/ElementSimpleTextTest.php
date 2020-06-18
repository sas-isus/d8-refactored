<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementSimpleText;

/**
 * @group GatherContentClient
 */
class ElementSimpleTextTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementSimpleText::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'value' => 'b',
        ];
        $cases['basic'][1] = [
            'value' => 'b',
        ];

        return $cases;
    }

    public function testGetSetValue()
    {
        /** @var \Cheppers\GatherContent\DataTypes\ElementSimpleText $element */
        $element = new $this->className([]);

        static::assertEquals('', $element->value);
        static::assertEquals('', $element->getValue());

        $element->setValue('a');
        static::assertEquals('a', $element->value);
        static::assertEquals('a', $element->getValue());

        $element->value = 'b';
        static::assertEquals('b', $element->getValue());
    }
}
