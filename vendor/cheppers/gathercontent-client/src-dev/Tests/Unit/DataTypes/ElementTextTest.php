<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementText;

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
            'limitType' => 'a',
            'limit' => 42,
            'plainText' => true,
            'value' => 'b',
        ];
        $cases['basic'][1] = [
            'limit_type' => 'a',
            'limit' => 42,
            'plain_text' => true,
            'value' => 'b',
        ];

        return $cases;
    }

    public function testGetSetValue()
    {
        /** @var \Cheppers\GatherContent\DataTypes\ElementText $element */
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
