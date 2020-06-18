<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\ElementBase;

/**
 * @group GatherContentClient
 */
class ElementBaseTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = ElementBase::class;

    public function testGetSetValue()
    {
        /** @var \Cheppers\GatherContent\DataTypes\ElementBase $element */
        $element = new $this->className([]);

        static::assertEquals('', $element->getValue());

        $element->setValue('a');
        static::assertEquals('', $element->getValue());
    }
}
