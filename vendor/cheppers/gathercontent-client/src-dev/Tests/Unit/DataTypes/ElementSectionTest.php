<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementSection;

/**
 * @group GatherContentClient
 */
class ElementSectionTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementSection::class;

    /**
     * {@inheritdoc}
     */
    public function casesConstructor()
    {
        $cases = parent::casesConstructor();
        $cases['basic'][0] = [
            'id' => 'a',
            'type' => 'section',
            'title' => 'b',
            'subtitle' => 'c',
        ];
        $cases['basic'][1] = [
            'name' => 'a',
            'type' => 'section',
            'title' => 'b',
            'subtitle' => 'c',
        ];

        return $cases;
    }

    public function testGetSetValue()
    {
        /** @var \Cheppers\GatherContent\DataTypes\ElementSection $element */
        $element = new $this->className([]);

        static::assertEquals(null, $element->getValue());
        $element->setValue('a');
        static::assertEquals(null, $element->getValue());
    }
}
