<?php

namespace Cheppers\GatherContent\Tests\Unit\DataTypes;

use Cheppers\GatherContent\DataTypes\Item;

/**
 * @group GatherContentClient
 */
class ItemTest extends BaseTest
{
    /**
     * {@inheritdoc}
     */
    protected $className = Item::class;

    public function testJsonSerialize()
    {
        $itemArray = static::getUniqueResponseItem([
            'text', 'files', 'choice_radio', 'choice_checkbox'
        ], static::getUniqueResponseStructure([
            ['text', 'files', 'choice_radio', 'choice_checkbox'],
        ]));

        $item1 = new $this->className($itemArray);

        $item1->name .= '-MODIFIED';
        $itemArray['name'] .= '-MODIFIED';

        $json1 = json_encode($item1);
        $actual1 = json_decode($json1, true);

        static::assertEquals(
            \GuzzleHttp\json_encode($item1, JSON_PRETTY_PRINT),
            \GuzzleHttp\json_encode($actual1, JSON_PRETTY_PRINT)
        );

        /** @var \Cheppers\GatherContent\DataTypes\Item $item2 */
        $item2 = new $this->className($actual1);
        $json2 = json_encode($item2);
        $actual2 = json_decode($json2, true);
        static::assertEquals($actual1, $actual2);
    }
}
