<?php

namespace Cheppers\GatherContent\Tests\Unit\Utils;

use Cheppers\GatherContent\Utils\NestedArray;
use PHPUnit\Framework\TestCase;

class NestedArrayTest extends TestCase
{
    public function casesTestGetValue()
    {
        return [
            'first_level' => [
                '10',
                ['parent_1' => '10'],
                ['parent_1'],
                true,
            ],
            'second_level' => [
                '10',
                ['parent_1' => ['parent_2' => '10']],
                ['parent_1', 'parent_2'],
                true,
            ],
            'key_missing' => [
                null,
                ['parent_1' => '10'],
                ['parent_2'],
                false,
            ],
        ];
    }

    /**
     * @dataProvider casesTestGetValue
     */
    public function testGetValue($expected, $array, $parents, $keyExists)
    {
        $returnedKeyExists = false;
        $returnedValue = NestedArray::getValue($array, $parents, $returnedKeyExists);

        static::assertEquals($expected, $returnedValue);
        static::assertEquals($keyExists, $returnedKeyExists);
    }
}
