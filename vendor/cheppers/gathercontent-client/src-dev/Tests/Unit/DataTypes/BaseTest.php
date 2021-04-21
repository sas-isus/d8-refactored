<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Base;
use Cheppers\GatherContent\Tests\Unit\GcBaseTestCase;

/**
 * @group GatherContentClient
 */
class BaseTest extends GcBaseTestCase
{
    /**
     * @var string
     */
    protected $className = Base::class;

    public function testPropertyMapping()
    {
        $className = $this->className;
        $instance = new $className();
        $class = new \ReflectionClass($instance);
        $publicProperties = $class->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($publicProperties as $key => $publicProperty) {
            if ($publicProperty->isStatic()) {
                unset($publicProperties[$key]);
            }
        }

        $propertyMapping = $class->getProperty('propertyMapping');
        $propertyMapping->setAccessible(true);

        $missing = [];
        $mapping = $propertyMapping->getValue($instance);
        foreach ($publicProperties as $property) {
            $name = $property->getName();
            $found = false;
            foreach ($mapping as $handler) {
                if ($handler['destination'] === $name) {
                    $found = true;

                    break;
                }
            }

            if (!$found) {
                $missing[] = $name;
            }
        }

        static::assertEquals([], $missing, 'All public property is mapped');
    }

    public function casesConstructor()
    {
        return [
            'empty' => [
                [
                    'id' => '',
                ],
                [],
            ],
            'basic' => [
                [
                    'id' => 'foo',
                ],
                [
                    'id' => 'foo',
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesConstructor
     */
    public function testConstructor(array $expected, array $data)
    {
        $date = new $this->className($data);
        foreach ($expected as $key => $value) {
            static::assertEquals($value, $date->{$key}, "Constructor - $key");
        }
    }
}
